<?php declare(strict_types=1);

namespace Sms77\Bolt;

use Bolt\Extension\BaseExtension;
use Sms77\Bolt\ReferenceWidget;

class Extension extends BaseExtension {
    /** Return the full name of the extension */
    public function getName(): string {
        return 'Sms77';
    }

    /**
     * Ran automatically, if the current request is in a browser.
     * You can use this method to set up things in your extension.
     * Note: This runs on every request. Make sure what happens here is quick
     * and efficient.
     */
    public function initialize($cli = false): void {
        $this->addWidget(new ReferenceWidget());
        $this->addTwigNamespace('sms77-bolt');
    }

    /**
     * Ran automatically, if the current request is from the command line (CLI).
     * You can use this method to set up things in your extension.
     * Note: This runs on every request. Make sure what happens here is quick
     * and efficient.
     */
    public function initializeCli(): void {
    }

    /**
     * @return array
     */
    protected function registerMenuEntries() {
        $entry = new MenuEntry('sms77-menu', 'sms77');
        $entry
            ->setLabel('Sms77 - Settings')
            ->setIcon('fa:cogs')
            ->setPermission('settings');

        return [$entry];
    }
}