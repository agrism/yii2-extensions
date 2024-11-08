<?php
namespace deele\extensions\models;

/**
 * @property string $name
 */
class BaseExtensionComponent extends ExtensionComponent
{
    protected ?string $_name = null;
    public function getName(): string
    {
        return $this->_name;
    }

    public function setName(string $name): void
    {
        $this->_name = $name;
    }
}
