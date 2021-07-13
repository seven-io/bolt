<?php declare(strict_types=1);

namespace Sms77\Bolt\Controller\Backend;

use Bolt\Configuration\Config;
use Bolt\Extension\ExtensionController;
use Bolt\Extension\ExtensionRegistry;
use Bolt\Repository\ContentRepository;
use Sms77\Bolt\Extension;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tightenco\Collect\Support\Collection;

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

    private function getApiKey(Collection $collection) {
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
            'X-Api-Key: ' . $this->getApiKey($this->getExtensionConfig()),
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    private function sms(string $to, string $text, array $data = []) {
        return $this->post('sms', array_merge($data, compact('text', 'to')));
    }

    private function handleBulkSms(Request $req, array $mappings): void {
        foreach ($mappings as $contentType => $phoneField) {
            $contents = $this->contentRepository->findBy(['contentType' => $contentType]);

            foreach ($contents as $content) {
                if (!$content->hasField($phoneField)) continue;
                $to = $content->getField($phoneField)->getValue();
                if (!$to || empty($to)) continue;

                $names = [];
                foreach ($content->getFields() as $field) $names[] = $field->getName();
                $text = $req->get('text');
                $matches = [];
                preg_match_all('{{{(' . implode('|', $names) . ')}}}', $text, $matches);

                if ($matches) foreach ($matches[1] as $match) {
                    if (!$content->hasField($match)) continue;
                    $value = $content->getField($match)->getValue();
                    if (!$value || empty($value)) continue;

                    $text = str_replace('{{' . $match . '}}', $value[0], $text);
                }

                $this->addFlash('notice',
                    $this->sms($to[0], $text, $this->getExtraSmsOptions($req->request)));
            }
        }
    }

    private function getExtraSmsOptions(InputBag $bag): array {
        $delay = $bag->get('delay');
        if ($delay) $delay = (new \DateTime($delay))->getTimestamp();

        return [
            'debug' => (int)$bag->getBoolean('debug'),
            'delay' => $delay,
            'label' => $bag->get('label'),
            'foreign_id' => $bag->get('foreign_id'),
            'flash' => (int)$bag->getBoolean('flash'),
            'from' => $bag->get('from'),
            'no_reload' => (int)$bag->getBoolean('no_reload'),
            'performance_tracking' => (int)$bag->getBoolean('performance_tracking'),
        ];
    }

    /** @Route("/sms77/bulk/sms", name="sms77_bulk_sms", methods={"GET", "POST"}) */
    public function bulk_sms(): Response {
        $cfg = $this->getExtensionConfig();
        $req = $this->getRequest();

        if ('POST' === $req->getMethod()) $this->handleBulkSms($req, $cfg['mappings']);

        return $this->render('@sms77-bolt/bulk_sms.html.twig', $cfg->toArray());
    }
}
