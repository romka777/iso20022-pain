<?php

namespace Consilience\Pain001\SupplementaryData;

/**
 * SupplementaryData.
 *
 * This this accept multiple custom namespaced documents supplied by the
 * application. A PAIN.001 supports an optional and unbounded number of
 * SplmtryData elements, each of a similar format: an optional plcAndNm
 * and one Envlp containing the namespaced extension.
 */

use Consilience\Pain001\SupplementaryDataInterface;
use DOMDocument;
use DOMElement;

class SupplementaryData implements SupplementaryDataInterface
{
    /**
     * string
     */
    protected $placeAndName;

    /**
     * string
     */
    protected $extensions = [];

    public function asDom(DOMDocument $doc) : DOMElement
    {
        // The root of this object.
        $splmtryData = $doc->appendChild($doc->createElement('SplmtryData'));

        // Optional PlcAndNm

        if ($this->getPlaceAndName() !== null) {
            $splmtryData->appendChild(
                $doc->createElement('PlcAndNm', $this->getPlaceAndName())
            );
        }

        // Mandatory Envlp with one or more childrn.

        $envelope = $splmtryData->appendChild($doc->createElement('Envlp'));

        foreach ($this->extensions as $extension) {
            $envelope->appendChild($extension->asDom($doc));
            //$envelope->appendChild($this->buildEnvelope($doc));
        }

        return $splmtryData;
    }

    public function getPlaceAndName()
    {
        return $this->placeAndName;
    }

    public function setPlaceAndName(string $value)
    {
        $this->placeAndName = $value;
        return $this;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
        return $this;
    }

    /**
     * Multiple extension documents can be added.
     *
     * @param array|ExtensionInterface $extension the DOM document to go into the envelope.
     */
    public function __construct($extension, string $placeAndName = null)
    {
        if ($placeAndName !== null) {
            $this->setPlaceAndName($placeAndName);
        }

        if (is_array($extension)) {
            foreach ($extension as $extensionDoc) {
                $this->addExtension($extensionDoc);
            }
        } else {
            $this->addExtension($extension);
        }
    }
}
