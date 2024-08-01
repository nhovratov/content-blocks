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

namespace TYPO3\CMS\ContentBlocks\Tests\Unit\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\ContentBlocks\Definition\Factory\ContentBlockCompiler;
use TYPO3\CMS\ContentBlocks\Definition\Factory\TableDefinitionCollectionFactory;
use TYPO3\CMS\ContentBlocks\Generator\SqlGenerator;
use TYPO3\CMS\ContentBlocks\Loader\ContentBlockLoader;
use TYPO3\CMS\ContentBlocks\Loader\LoadedContentBlock;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\ContentBlocks\Tests\Unit\Fixtures\FieldTypeRegistryTestFactory;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Schema\FieldTypeFactory;
use TYPO3\CMS\Core\Schema\RelationMapBuilder;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class SqlGeneratorTest extends UnitTestCase
{
    public static function generateReturnsExpectedSqlStatementsDataProvider(): iterable
    {
        yield 'simple fields in tt_content table' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 'foo_bar',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'number',
                                'type' => 'Number',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `tt_content`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
            ],
        ];

        yield 'simple fields in custom foobar table' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'foobar',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'number',
                                'type' => 'Number',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `foobar`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
            ],
        ];

        yield 'simple fields in custom foobar table with parent reference' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/parent',
                    'yaml' => [
                        'table' => 'tt_content',
                        'fields' => [
                            [
                                'identifier' => 'collection',
                                'type' => 'Collection',
                                'foreign_table' => 'foobar',
                                'shareAcrossTables' => true,
                                'shareAcrossFields' => true,
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'foobar',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'number',
                                'type' => 'Number',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `foobar`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
                "CREATE TABLE `foobar`(`foreign_table_parent_uid` int(11) DEFAULT '0' NOT NULL, KEY parent_uid (foreign_table_parent_uid));",
                "CREATE TABLE `foobar`(`tablenames` varchar(255) DEFAULT '' NOT NULL);",
                "CREATE TABLE `foobar`(`fieldname` varchar(255) DEFAULT '' NOT NULL);",
            ],
        ];

        yield 'simple fields in custom foobar table with typeField defined' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'foobar',
                        'typeField' => 'my_type',
                        'typeName' => 'foo',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'number',
                                'type' => 'Number',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `foobar`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
            ],
        ];

        yield 'nullable option removes NOT NULL statement' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 'foo_bar',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'number',
                                'type' => 'Number',
                                'nullable' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `tt_content`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
            ],
        ];

        yield 'inline field on root level' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 'foo_bar',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'collection',
                                'type' => 'Collection',
                                'fields' => [
                                    [
                                        'identifier' => 'text',
                                        'type' => 'Text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `foo_bar_collection`(`text` VARCHAR(255) DEFAULT '' NOT NULL);",
                "CREATE TABLE `foo_bar_collection`(`foreign_table_parent_uid` int(11) DEFAULT '0' NOT NULL, KEY parent_uid (foreign_table_parent_uid));",
                "CREATE TABLE `tt_content`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
            ],
        ];

        yield 'inline field on second level' => [
            'contentBlocks' => [
                [
                    'name' => 'foo/bar',
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 'foo_bar',
                        'prefixFields' => true,
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'catgeory',
                                'type' => 'Category',
                            ],
                            [
                                'identifier' => 'collection',
                                'type' => 'Collection',
                                'fields' => [
                                    [
                                        'identifier' => 'text',
                                        'type' => 'Text',
                                    ],
                                    [
                                        'identifier' => 'collection2',
                                        'type' => 'Collection',
                                        'fields' => [
                                            [
                                                'identifier' => 'text',
                                                'type' => 'Text',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                "CREATE TABLE `collection2`(`text` VARCHAR(255) DEFAULT '' NOT NULL);",
                "CREATE TABLE `collection2`(`foreign_table_parent_uid` int(11) DEFAULT '0' NOT NULL, KEY parent_uid (foreign_table_parent_uid));",
                "CREATE TABLE `foo_bar_collection`(`text` VARCHAR(255) DEFAULT '' NOT NULL);",
                "CREATE TABLE `foo_bar_collection`(`foreign_table_parent_uid` int(11) DEFAULT '0' NOT NULL, KEY parent_uid (foreign_table_parent_uid));",
                "CREATE TABLE `tt_content`(`foo_bar_text` VARCHAR(255) DEFAULT '' NOT NULL);",
            ],
        ];
    }

    #[DataProvider('generateReturnsExpectedSqlStatementsDataProvider')]
    #[Test]
    public function generateReturnsExpectedSqlStatements(array $contentBlocks, array $expected): void
    {
        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $cacheMock = $this->createMock(PhpFrontend::class);
        $cacheMock->method('has')->with(self::isType('string'))->willReturn(false);
        $tcaSchemaFactory = new TcaSchemaFactory(
            new RelationMapBuilder(),
            new FieldTypeFactory(),
            '',
            $cacheMock
        );
        $contentBlockRegistry = new ContentBlockRegistry();
        foreach ($contentBlocks as $contentBlock) {
            $contentBlockRegistry->register(LoadedContentBlock::fromArray($contentBlock));
        }
        $loader = $this->createMock(ContentBlockLoader::class);
        $loader->method('loadUncached')->willReturn($contentBlockRegistry);
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollectionFactory = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler));
        $sqlGenerator = new SqlGenerator($loader, $tableDefinitionCollectionFactory, $tcaSchemaFactory, $fieldTypeRegistry);

        $result = $sqlGenerator->generate();

        self::assertSame($expected, $result);
    }
}
