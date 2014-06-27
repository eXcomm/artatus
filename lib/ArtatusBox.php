<?php

class ArtatusBox
{

    public function __construct($aReference, $aOuterWidth, $aOuterLength, $aOuterDepth, $aEmptyWeight, $aInnerWidth, $aInnerLength, $aInnerDepth, $aMaxWeight)
    {
        $this->reference = $aReference;
        $this->outerWidth = $aOuterWidth;
        $this->outerLength = $aOuterLength;
        $this->outerDepth = $aOuterDepth;
        $this->emptyWeight = $aEmptyWeight;
        $this->innerWidth = $aInnerWidth;
        $this->innerLength = $aInnerLength;
        $this->innerDepth = $aInnerDepth;
        $this->maxWeight = $aMaxWeight;
        $this->innerVolume = $this->innerWidth * $this->innerLength * $this->innerDepth;
    }


    /**
     * Reference for box type (e.g. SKU or description)
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Outer width in mm
     * @return int
     */
    public function getOuterWidth()
    {
        return $this->outerWidth;
    }

    /**
     * Outer length in mm
     * @return int
     */
    public function getOuterLength()
    {
        return $this->outerLength;
    }

    /**
     * Outer depth in mm
     * @return int
     */
    public function getOuterDepth()
    {
        return $this->outerDepth;
    }

    /**
     * Empty weight in g
     * @return int
     */
    public function getEmptyWeight()
    {
        return $this->emptyWeight;
    }

    /**
     * Inner width in mm
     * @return int
     */
    public function getInnerWidth()
    {
        return $this->innerWidth;
    }

    /**
     * Outer length in mm
     * @return int
     */
    public function getInnerLength()
    {
        return $this->innerLength;
    }

    /**
     * Outer depth in mm
     * @return int
     */
    public function getInnerDepth()
    {
        return $this->innerDepth;
    }

    /**
     * Total inner volume of packing in mm^3
     * @return int
     */
    public function getInnerVolume()
    {
        return $this->innerVolume;
    }

    /**
     * Max weight the packaging can hold in g
     * @return int
     */
    public function getMaxWeight()
    {
        return $this->maxWeight;
    }
}

