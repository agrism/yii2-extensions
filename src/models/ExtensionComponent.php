<?php

namespace deele\extensions\models;

use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\console\Application as ConsoleApplication;

abstract class ExtensionComponent extends Component implements BootstrapInterface
{
    public bool $active = false;
    public int $orderNumber = 0;

    abstract public function getName(): string;

    public function activate(): bool
    {
        return false;
    }

    public function deactivate(): bool
    {
        return false;
    }

    public function install(): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public static function extensionDirPath(
        string $extensionName
    ): ?string
    {
        $extensionDir = implode(DIRECTORY_SEPARATOR, [
            \Yii::getAlias('@extensions'),
            $extensionName
        ]);
        return (file_exists($extensionDir) ? $extensionDir : null);
    }

    public static function fetchClass(
        string $extensionDirPath,
        string $extensionName
    ): string
    {
        $extensionClassName = ucfirst($extensionName) . 'Extension';
        $extensionClassFile = $extensionDirPath . DIRECTORY_SEPARATOR . $extensionClassName . '.php';
        if (file_exists($extensionClassFile)) {
            return $extensionName . '\\' . $extensionClassName;
        }
        return BaseExtensionComponent::class;
    }

    public static function fetchApplicationConfig(
        string $extensionDirPath,
        string $applicationId
    ): array
    {
        $webConfigFile = $extensionDirPath . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR .
            $applicationId . '.php';
        if (file_exists($webConfigFile)) {
            return require($webConfigFile);
        }
        return [];
    }

    public function loadMigrations(ConsoleApplication $app): void
    {
        $migrationsLocation = static::extensionDirPath($this->getName()) . DIRECTORY_SEPARATOR . 'migrations';
        if (isset($app->controllerMap['migrate']['migrationNamespaces']) && file_exists($migrationsLocation)) {
            $app->controllerMap['migrate']['migrationNamespaces'][$this->getName()] = $this->getName() . '\migrations';
        }
    }

    public function bootstrap($app): void
    {
        if (($app instanceof ConsoleApplication)) {
            $this->loadMigrations($app);
        }
    }
}
