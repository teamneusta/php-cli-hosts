<?php

namespace TeamNeusta\Hosts\Exception;

class ConfigurationAlreadyExistException extends \IOException
{
    public function __construct(
        private string $scope = 'local'
    ) {
        parent::__construct(
            message: sprintf('Configuration for scope "%s" already exist.', $this->scope),
            code: 0,
            previous: null
        );
    }

    public function getScope(): string
    {
        return $this->scope;
    }
}