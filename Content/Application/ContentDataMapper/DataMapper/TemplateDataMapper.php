<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class TemplateDataMapper implements DataMapperInterface
{
    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    /**
     * @var array<string, string>
     */
    private $structureDefaultTypes;

    /**
     * @param array<string, string> $structureDefaultTypes
     */
    public function __construct(StructureMetadataFactoryInterface $factory, array $structureDefaultTypes)
    {
        $this->factory = $factory;
        $this->structureDefaultTypes = $structureDefaultTypes;
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data
    ): void {
        if (!$localizedDimensionContent instanceof TemplateInterface
            || !$unlocalizedDimensionContent instanceof TemplateInterface
        ) {
            return;
        }

        $type = $localizedDimensionContent::getTemplateType();

        /** @var string|null $template */
        $template = $data['template'] ?? null;

        if (null === $template) {
            $template = $this->structureDefaultTypes[$type] ?? null;
        }

        if (null === $template) {
            throw new \RuntimeException('Expected "template" to be set in the data array.');
        }

        list($unlocalizedData, $localizedData, $hasAnyValue) = $this->getTemplateData(
            $data,
            $type,
            $template
        );

        if (!isset($data['template']) && !$hasAnyValue) {
            // do nothing when no data was given
            return;
        }

        $localizedDimensionContent->setTemplateKey($template);
        $localizedDimensionContent->setTemplateData($localizedData);

        $unlocalizedDimensionContent->setTemplateData(\array_merge(
            $unlocalizedDimensionContent->getTemplateData(),
            $unlocalizedData
        ));
    }

    /**
     * @param mixed[] $data
     *
     * @return array{
     *     0: mixed[],
     *     1: mixed[],
     *     2: bool,
     * }
     */
    private function getTemplateData(array $data, string $type, string $template): array
    {
        $metadata = $this->factory->getStructureMetadata($type, $template);

        if (!$metadata) {
            throw new \RuntimeException(\sprintf('Could not find structure "%s" of type "%s".', $template, $type));
        }

        $unlocalizedData = [];
        $localizedData = [];
        $hasAnyValue = false;

        foreach ($metadata->getProperties() as $property) {
            $value = null;
            $name = $property->getName();

            // Float are converted to ints in php array as key so we need convert it to string
            if (\is_float($name)) {
                $name = (string) $name;
            }

            if (\array_key_exists($name, $data)) {
                $hasAnyValue = true;
                $value = $data[$name];
            }

            if ($property->isLocalized()) {
                $localizedData[$name] = $value;
                continue;
            }

            $unlocalizedData[$name] = $value;
        }

        return [$unlocalizedData, $localizedData, $hasAnyValue];
    }
}
