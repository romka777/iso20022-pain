<?php

namespace Consilience\Pain002\Message;

use Consilience\Pain001\Money\Mixed;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Exception;

/**
 * General pain.002 message
 */
abstract class AbastractMessage
{
    const ASSERT_REQUIRED = 'required';

    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var boolean
     */
    protected $parsingFailed = false;

    /**
     * @var string
     */
    protected $failureMessage;

    /**
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * @return boolean true if parsing failed for any reason.
     */
    public function parsingFailed()
    {
        return $this->parsingFailed;
    }

    /**
     * Sets the failure flag and a failure reason message.
     *
     * @param string|null $message Optional error or reason message.
     * @return $this
     */
    public function withFailure(string $message = null)
    {
        $this->parsingFailed = true;

        if ($message !== null) {
            $this->failureMessage = $message;
        }

        return $this;
    }

    /**
     * Return the message to help dianose why the parsing failed.
     * @return string
     */
    public function getFailureMessage()
    {
        return $this->failureMessage;
    }

    /**
     * @param DOMDocument $dom
     */
    public function setDom(DOMDocument $dom)
    {
        $this->dom = $dom;

        // XPath to parse the message.
        // TODO: set up xpath in the dom constructor.

        $this->xpath = new DOMXPath($dom);

        // Register the namespaces.
        // FIXME: then we make the assumption that the xmlns alias
        // is used in this supplied document.

        foreach ($this->xpath->query('namespace::*', $dom->documentElement) as $node) {
            $this->xpath->registerNamespace($node->localName, $node->nodeValue);
        }

        return $this;
    }

    /**
     * Get all child elements of a node, optionally with a given name.
     */
    protected function getChildElements(DOMNode $node, string $name = '*')
    {
        $children = $this->xpath->query('xmlns:' . $name, $node);

        return $children;
    }

    /**
     * Get a single child element of a node.
     */
    protected function getChildElement(DOMNode $node, string $name, array $assert = [])
    {
        $children = $this->getChildElements($node, $name);

        $count = $children->count();

        if (in_array(static::ASSERT_REQUIRED, $assert) && ! $count) {
            throw new Exception(sprintf(
                'Missing element "%s" expected at "%s" on line %d',
                $name,
                $node->getNodePath(),
                $node->getLineNo()
            ));
        }

        return $children->item(0);
    }

    /**
     * Get a single child element of a node.
     */
    protected function getChildElementValue(DOMNode $node, string $name, array $assert = [])
    {
        $child = $this->getChildElement($node, $name, $assert);

        return $child->nodeValue ?? null;
    }

    /**
     * @return DOMDocument|null
     */
    public function getDom()
    {
        return $this->dom;
    }
}
