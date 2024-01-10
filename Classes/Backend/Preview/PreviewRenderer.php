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

namespace TYPO3\CMS\ContentBlocks\Backend\Preview;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataResolver;
use TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentType;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\ContentBlocks\Utility\ContentBlockPathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Sets up Fluid and applies the same DataProcessor as the frontend to the data record.
 *
 * @internal Not part of TYPO3's public API.
 */
class PreviewRenderer extends StandardContentPreviewRenderer
{
    public function __construct(
        protected readonly TableDefinitionCollection $tableDefinitionCollection,
        protected readonly ContentBlockRegistry $contentBlockRegistry,
        protected readonly ContentBlockDataResolver $contentBlockDataResolver,
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $record = $item->getRecord();
        $typeField = ContentType::CONTENT_ELEMENT->getTypeField();
        $typeName = $record[$typeField];
        $contentElementDefinition = $this->tableDefinitionCollection->getContentElementDefinition($typeName);
        $contentBlockPath = $this->contentBlockRegistry->getContentBlockExtPath($contentElementDefinition->getName());
        $contentBlockPrivatePath = $contentBlockPath . '/' . ContentBlockPathUtility::getPrivateFolder();

        // Fall back to standard preview rendering if EditorPreview.html does not exist.
        if (!file_exists(GeneralUtility::getFileAbsFileName($contentBlockPath . '/' . ContentBlockPathUtility::getBackendPreviewPath()))) {
            return parent::renderPageModulePreviewContent($item);
        }
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setLayoutRootPaths([$contentBlockPrivatePath . '/Layouts/']);
        $view->setPartialRootPaths([
            'EXT:backend/Resources/Private/Partials/',
            'EXT:content_blocks/Resources/Private/Partials/',
            $contentBlockPrivatePath . '/Partials/',
        ]);
        $view->setTemplateRootPaths([$contentBlockPrivatePath]);
        $view->setTemplate(ContentBlockPathUtility::getBackendPreviewFileNameWithoutExtension());
        $view->setRequest($request);

        $contentElementTable = ContentType::CONTENT_ELEMENT->getTable();
        $contentElementTableDefinition = $this->tableDefinitionCollection->getTable($contentElementTable);

        $this->contentBlockDataResolver->setRequest($request);
        $data = $this->contentBlockDataResolver->buildContentBlockDataObjectRecursive(
            $contentElementDefinition,
            $contentElementTableDefinition,
            $record,
            $contentElementTable,
            context: $item->getContext(),
        );

        $view->assign('data', $data);

        return $view->render();
    }
}
