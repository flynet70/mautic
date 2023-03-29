<?php

namespace Mautic\CoreBundle\Twig\Helper;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

final class ContentHelper
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * UIHelper constructor.
     */
    public function __construct(Environment $twig, EventDispatcherInterface $dispatcher)
    {
        $this->twig       = $twig;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dispatch an event to collect custom content.
     *
     * @param string|null $context  Context of the content requested for the viewName
     * @param array<string,string> $vars     twig vars
     * @param string|null $viewName The main identifier for the content requested. Will be etracted from $vars if get_
     *defined
     *
     * @return string
     */
    public function getCustomContent($context = null, array $vars = [], $viewName = null): string
    {
        if (null === $viewName) {
            if (empty($vars['mauticTemplate'])) {
                return '';
            }

            $viewName = $vars['mauticTemplate'];
        }

        /** @var CustomContentEvent $event */
        $event = $this->dispatcher->dispatch(
            new CustomContentEvent($viewName, $context, $vars),
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT
        );

        $content = $event->getContent();

        if ($templatProps = $event->getTemplates()) {
            foreach ($templatProps as $props) {
                $content[] = $this->twig->render($props['template'], array_merge($vars, $props['vars']));
            }
        }

        return implode("\n\n", $content);
    }

    /**
     * Replaces HTML script tags with non HTML tags so the JS inside them won't execute and will be readable.
     *
     * @param string $html
     *
     * @return string
     */
    public function showScriptTags($html): string
    {
        $tagsToShow = ['script', 'style'];

        foreach ($tagsToShow as $tag) {
            $html = preg_replace('/<'.$tag.'(.*?)>(.*?)<\/'.$tag.'>/s', '['.$tag.'$1]$2[/'.$tag.']', $html);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'content';
    }
}
