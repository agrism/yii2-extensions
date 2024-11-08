<?php
namespace deele\extensions\components;

use DirectoryIterator;
use yii\base\BootstrapInterface;
use yii\base\Component;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

class ExtensionsManager extends Component implements BootstrapInterface
{
    public string $extensionsStorage = '@app/extensions';

    public function bootstrap($app): void
    {
//        $this
//            ->prepareExtensionsAlias()
//            ->loadEnabledExtensions();
    }

    public function prepareExtensionsAlias(): self
    {
        Yii::setAlias('@extensions', $this->extensionsStorage);
        return $this;
    }

    /**
     * @param string $name
     * @param bool $validate
     *
     * @return array|null
     *
     * @throws InvalidArgumentException if there is any decoding error
     */
    public function installedExtensionData(string $name, bool $validate = true): ?array
    {
        $extensionBaseDir = Yii::getAlias('@extensions') . DIRECTORY_SEPARATOR . $name;
        if ($validate === false || (file_exists($extensionBaseDir) && is_dir($extensionBaseDir))) {
            $extensionConfigFilePath = $extensionBaseDir . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($extensionConfigFilePath) && is_readable($extensionConfigFilePath)) {
                return Json::decode(file_get_contents($extensionConfigFilePath));
            }
        }
        return null;
    }

    /**
     * @return string[]
     */
    public function installedExtensionNames(): array
    {
        $extensions = [];
        foreach ((new DirectoryIterator(Yii::getAlias('@extensions'))) as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                $extensions[] = $fileInfo->getFilename();
            }
        }
        return $extensions;
    }

    /**
     * @return string[]
     */
    public function installedExtensionsData(): array
    {
        $extensions = [];
        foreach ($this->installedExtensionNames() as $directoryName) {
            try {
                $extensions[$directoryName] = $this->installedExtensionData($directoryName, false);
            } catch (InvalidArgumentException $exception) {
                Yii::warning(sprintf(
                    'Could not load "%s" extension config: %s',
                    $directoryName,
                    $exception->getMessage()
                ));
                $extensions[$directoryName] = null;
            }
        }
        return $extensions;
    }

    public function loadEnabledExtensions(): self
    {
        foreach ($this->installedExtensionsData() as $extensionName => $extensionData) {
            if ($extensionData === null) {
                Yii::info(
                    'No data about "' . $extensionName . '" extension'
                );
                continue;
            }
            Yii::info($extensionName . ': ' . \yii\helpers\VarDumper::dumpAsString($extensionData));
        }
        return $this;
    }
}
