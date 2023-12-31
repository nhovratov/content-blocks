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

namespace TYPO3\CMS\ContentBlocks\Generator;

use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Imaging\IconRegistry;

/**
 * @internal Not part of TYPO3's public API.
 */
class IconGenerator
{
    public function __construct(
        protected readonly TableDefinitionCollection $tableDefinitionCollection,
        protected readonly IconRegistry $iconRegistry,
    ) {}

    public function __invoke(BootCompletedEvent $event): void
    {
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->getContentTypeDefinitionCollection() ?? [] as $typeDefinition) {
                $this->iconRegistry->registerIcon(
                    identifier: $typeDefinition->getTypeIconIdentifier(),
                    iconProviderClassName: $typeDefinition->getIconProviderClassName(),
                    options: ['source' => $typeDefinition->getTypeIconPath()],
                );
            }
        }
    }
}
