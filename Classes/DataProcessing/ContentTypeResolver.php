<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\ContentBlocks\DataProcessing;

use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;

/**
 * @internal Not part of TYPO3's public API.
 */
class ContentTypeResolver
{
    public static function resolve(TableDefinition $tableDefinition, array $record): ?ContentTypeInterface
    {
        $typeName = $tableDefinition->getTypeField() !== null
            ? ($record[$tableDefinition->getTypeField()] ?? null)
            : '1';
        if ($typeName === null) {
            return null;
        }
        $typeDefinitionCollection = $tableDefinition->getTypeDefinitionCollection();
        if (!$typeDefinitionCollection->hasType($typeName)) {
            return null;
        }
        $typeDefinition = $typeDefinitionCollection->getType($typeName);
        return $typeDefinition;
    }
}