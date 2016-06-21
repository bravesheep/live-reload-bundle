<?php

namespace Bravesheep\LiveReloadBundle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ScriptInjectorSubscriber implements EventSubscriberInterface
{
    const BODY_END_TAG = '</body>';

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $host
     * @param int    $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -255]
        ];
    }


    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $response = $event->getResponse();
            $request = $event->getRequest();

            if ($this->isHtmlRequest($request) && $this->isHtmlResponse($response)) {
                $this->injectScript($response, $request);
            }
        }
    }

    /**
     * Check if the request is for a html page.
     *
     * @param Request $request
     * @return bool
     */
    private function isHtmlRequest(Request $request)
    {
        return !$request->isXmlHttpRequest() && $request->getRequestFormat() === 'html';
    }

    /**
     * Check if the response is a html page.
     *
     * @param Response $response
     * @return bool
     */
    private function isHtmlResponse(Response $response)
    {
        $headers = $response->headers;

        // assumes responses without Content-Type header are html
        return !$response->isRedirection() && (
            !$headers->has('Content-Type') || strpos($headers->get('Content-Type'), 'html') !== false
        );
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getScriptLocation(Request $request)
    {
        $host = $this->host !== null ? $this->host : $request->getHost();

        return "http://$host:{$this->port}/livereload.js";
    }

    /**
     * Add the livereload script tag just before the closing body tag.
     *
     * @param Response $response
     * @param Request  $request
     */
    private function injectScript(Response $response, Request $request)
    {
        $content = $response->getContent();

        if (function_exists('mb_strripos')) {
            $pos = mb_strripos($content, self::BODY_END_TAG);
        } else {
            $pos = strripos($content, self::BODY_END_TAG);
        }

        if (false !== $pos) {
            $script = $this->getScriptLocation($request);
            $scriptTag = "\n<script src=\"{$script}\"></script>\n";

            if (function_exists('mb_substr')) {
                $content = mb_substr($content, 0, $pos) . $scriptTag . mb_substr($content, $pos);
            } else {
                $content = substr($content, 0, $pos) . $scriptTag . substr($content, $pos);
            }

            $response->setContent($content);
        }
    }
}
