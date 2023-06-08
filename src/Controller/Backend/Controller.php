<?php declare(strict_types=1);

namespace Seven\Bolt\Controller\Backend;

use Bolt\Configuration\Config;
use Bolt\Extension\ExtensionController;
use Bolt\Extension\ExtensionRegistry;
use Bolt\Repository\ContentRepository;
use DateTime;
use Exception;
use Seven\Bolt\Extension;
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

    /**
     * @return Collection
     */
    private function getExtensionConfig(): Collection {
        return $this->registry->getExtension(Extension::class)->getConfig();
    }

    /**
     * @return string
     * @var Collection $collection
     */
    private function getApiKey(Collection $collection): string {
        return $collection->get('apiKey');
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return bool|string
     */
    private function post(string $endpoint, array $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gateway.seven.io/api/' . $endpoint);
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

    /**
     * @param string $endpoint
     * @param string $to
     * @param string $text
     * @param array $data
     * @return bool|string
     */
    private function message(string $endpoint, string $to, string $text, array $data = []) {
        return $this->post($endpoint, array_merge($data, compact('text', 'to')));
    }

    /**
     * @param string $type
     * @param Request $req
     * @param array $mappings
     * @throws Exception
     */
    private function handleBulk(string $type, Request $req, array $mappings): void {
        $extra = $this->getExtraOptions($req->request, 'sms' === $type);

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

                $this->addFlash('notice', $this->message($type, $to[0], $text, $extra));
            }
        }
    }

    /**
     * @param InputBag $bag
     * @param bool $isSMS
     * @return array
     * @throws Exception
     */
    private function getExtraOptions(InputBag $bag, bool $isSMS): array {
        $extras = [
            'debug' => (int)$bag->getBoolean('debug'),
            'from' => $bag->get('from'),
        ];

        if ($isSMS) {
            $delay = $bag->get('delay');
            if ($delay) $delay = (new DateTime($delay))->getTimestamp();

            $extras = array_merge($extras, [
                'delay' => $delay,
                'foreign_id' => $bag->get('foreign_id'),
                'flash' => (int)$bag->getBoolean('flash'),
                'label' => $bag->get('label'),
                'no_reload' => (int)$bag->getBoolean('no_reload'),
                'performance_tracking' => (int)$bag->getBoolean('performance_tracking'),
            ]);
        } else $extras['xml'] = (int)$bag->getBoolean('xml');

        return $extras;
    }

    /**
     * @Route("/seven/bulk/sms", name="seven_bulk_sms", methods={"GET", "POST"})
     * @return Response
     * @throws Exception
     */
    public function bulk_sms(): Response {
        return $this->bulk('sms');
    }

    /**
     * @Route("/seven/bulk/voice", name="seven_bulk_voice", methods={"GET", "POST"})
     * @return Response
     * @throws Exception
     */
    public function bulk_voice(): Response {
        return $this->bulk('voice');
    }

    private function bulk(string $type): Response {
        $cfg = $this->getExtensionConfig();
        $req = $this->getRequest();

        if ('POST' === $req->getMethod()) $this->handleBulk($type, $req, $cfg['mappings']);

        return $this->render('@seven-bolt/bulk_' . $type . '.html.twig', $cfg->toArray());
    }
}
