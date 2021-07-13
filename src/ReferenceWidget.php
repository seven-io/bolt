<?php declare(strict_types=1);

namespace Sms77\Bolt;

use Bolt\Widget\BaseWidget;
use Bolt\Widget\CacheAwareInterface;
use Bolt\Widget\CacheTrait;
use Bolt\Widget\Injector\AdditionalTarget;
use Bolt\Widget\Injector\RequestZone;
use Bolt\Widget\StopwatchAwareInterface;
use Bolt\Widget\StopwatchTrait;
use Bolt\Widget\TwigAwareInterface;

class ReferenceWidget extends BaseWidget
    implements TwigAwareInterface, CacheAwareInterface, StopwatchAwareInterface {
    use CacheTrait;
    use StopwatchTrait;

    protected $cacheDuration = -1800;
    protected $name = 'Sms77 BackWidget';
    protected $priority = 200;
    protected $target = AdditionalTarget::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
    protected $template = '@sms77-bolt/widget.html.twig';
    protected $zone = RequestZone::BACKEND;
}
