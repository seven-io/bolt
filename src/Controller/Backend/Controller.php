<?php declare(strict_types=1);

namespace Sms77\Bolt\Controller\Backend;

use Bolt\Configuration\Config;
use Bolt\Extension\ExtensionController;
use Bolt\Extension\ExtensionRegistry;
use Bolt\Repository\ContentRepository;
use Bolt\Utils\Sanitiser;
use Sms77\Bolt\Extension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tightenco\Collect\Support\Collection;
use Twig\Environment;

class Controller extends ExtensionController {
    public function __construct(Config            $config, ExtensionRegistry $registry,
                                ContentRepository $contentRepository) {
        parent::__construct($config);

        $this->registry = $registry;
        $this->contentRepository = $contentRepository;
    }

    private function getExtensionConfig(): Collection {
        return $this->registry->getExtension(Extension::class)->getConfig();
    }

    private function getApiKey(?Collection $collection) {
        if (!$collection) $collection = $this->getExtensionConfig();

        return $collection->get('apiKey');
    }

    private function post(string $endpoint, array $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.sms77.io/api/' . $endpoint);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-type: application/json',
            'SentWith: BoltCMS',
            'X-Api-Key: ' . $this->getApiKey(),
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    private function sms(array $data) {
        return $this->post('sms', $data);
    }

    /** @Route("/sms77/bulk/sms", name="sms77_bulk_sms", methods={"GET", "POST"}) */
    public function bulk_sms(): Response {
        $cfg = $this->getExtensionConfig();
        $req = $this->getRequest();

        if ('POST' === $req->getMethod())
            foreach ($cfg['mappings'] as $contentType => $phoneField) {
                $contents = $this->contentRepository->findBy(['contentType' => $contentType]);

                foreach ($contents as $content) {
                    if (!$content->hasField($phoneField)) continue;
                    $to = $content->getField($phoneField)->getValue();
                    if (!$to) continue;
                    $to = $to[0];

                    $fieldNames = [];
                    foreach ($content->getFields() as $field)
                        $fieldNames[] = $field->getName();

                    $text = $req->get('text');
                    $fieldNames = implode('|', $fieldNames);
                    $pattern = '{{{(' . $fieldNames . ')}}}';
                    $matches = [];
                    preg_match_all($pattern, $text, $matches);

                    foreach ($matches[1] as $match) {
                        $value = $content->getField($match)->getValue();
                        if (!$value || empty($value)) continue;

                        $text = str_replace('{{' . $match . '}}', $value[0], $text);
                    }

                    $this->addFlash('notice', $this->sms(compact('text', 'to')));
                }
            }

        return $this->render('@sms77-bolt/bulk_sms.html.twig', $cfg->toArray());
    }
}
