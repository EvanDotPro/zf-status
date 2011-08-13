<?php
namespace BaseApp;
abstract class DiContainerAbstract
{
    /**
     * Container's storage
     *
     * @var array
     */
    protected $_storage;

    /**
     * _options 
     * 
     * @var array
     */
    protected $_options;

    /**
     * __construct 
     * 
     * @param array $options 
     * @return void
     */
    public function __construct($options)
    {
        $this->setOptions($options);
    }
 
    /**
     * Get options.
     *
     * @return options
     */
    public function getOptions()
    {
        return $this->_options;
    }
 
    /**
     * Set options.
     *
     * @param $options the value to be set
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }
}
