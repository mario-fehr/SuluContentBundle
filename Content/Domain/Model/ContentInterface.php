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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

use Doctrine\Common\Collections\Collection;

interface ContentInterface
{
    public static function getResourceKey(): string;

    /**
     * @return Collection<ContentDimensionInterface>
     */
    public function getDimensions(): Collection;

    public function createDimension(string $dimensionId): ContentDimensionInterface;

    public function addDimension(ContentDimensionInterface $contentDimension): void;

    public function removeDimension(ContentDimensionInterface $contentDimension): void;
}
