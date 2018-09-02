<?php

namespace Consilience\Pain001\Message;

use Consilience\Pain001\MessageInterface;
use Consilience\Pain001\Text;

/**
 * AbstractMessages eases message creation using DOM
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * Builds the DOM of the actual message
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement
     */
    abstract protected function buildDom(\DOMDocument $doc);

    /**
     * Gets the name of the schema
     *
     * @return string
     */
    abstract protected function getSchemaName();

    /**
     * Gets the location of the schema
     *
     * @return string|null The location or null
     */
    abstract protected function getSchemaLocation();

    /**
     * Builds a DOM document of the message
     *
     * @return \DOMDocument
     */
    public function asDom()
    {
        $schema = $this->getSchemaName();
        $location = $this->getSchemaLocation();

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $root = $doc->createElement('Document');
        // Remove the $ from the schema for the xmlns.
        // I have no idea why it is there, what it does, why it must be
        // in the schemaLocation but not the xmlns attribute. It just does.
        $root->setAttribute('xmlns', str_replace('$', '', $schema));
        if ($location !== null) {
            $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $root->setAttribute('xsi:schemaLocation', $schema.' '.$location);
        }
        $root->appendChild($this->buildDom($doc));
        $doc->appendChild($root);

        return $doc;
    }

    /**
     * {@inheritdoc}
     */
    public function asXml(bool $format = false)
    {
        $dom = $this->asDom();

        if ($format) {
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
        }

        return $dom->saveXML();
    }

    /**
     * Default cast to string results in XML compact form.
     */
    public function __toString()
    {
        return $this->asXml();
    }

    /**
     * Returns the name of the software used to create the message.
     * Used to build the Initiating Party Contact Details, and is
     * disabled for now.
     *
     * @return string
     */
    public function getSoftwareName()
    {
        return;
    }

    /**
     * Returns the version of the software used to create the message
     * Used to build the Initiating Party Contact Details, and is
     * disabled for now.
     *
     * @return string
     */
    public function getSoftwareVersion()
    {
        return;
    }

    /**
     * Creates a DOM element which contains details about the software used
     * to create the message.
     * JDJ: this actually looks wrong. It has nothing to do with the software.
     * It appears more about a means to get in touch with the initiator of the
     * payment request, which may or may not be the debtor.
     * Examples invariably involve the account holder title/name/email etc.
     *
     * Any combination of these elements are allowed:
     * NmPrfx, Nm, PhneNb, MobNb, FaxNb, EmailAdr, Othr
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement|null
     */
    protected function buildContactDetails(\DOMDocument $doc)
    {
        $root = $doc->createElement('CtctDtls');

        if ($this->getSoftwareName() !== null) {
            $root->appendChild(Text::xml($doc, 'Nm', $this->getSoftwareName()));
        }

        if ($this->getSoftwareVersion() !== null) {
            $root->appendChild(Text::xml($doc, 'Othr', $this->getSoftwareVersion()));
        }

        return $root;
    }
}
