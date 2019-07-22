<?php
/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 *
 */

declare(strict_types=1);

namespace Propel\Generator\Schema;

use Propel\Generator\Model\Model;
use Propel\Generator\Model\NamingTool;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Class SchemaConfiguration
 *
 * This class performs validation of schema array and assign default values
 *
 */
class SchemaConfiguration implements ConfigurationInterface
{
    /**
     * Generates the schema tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('database');
        $rootNode
                ->fixXmlConfig('entity', 'entities')
                ->fixXmlConfig('behavior')
                ->fixXmlConfig('external_schema', 'external-schemas')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                    ->enumNode('defaultIdMethod')
                        ->values([Model::ID_METHOD_NONE, Model::ID_METHOD_NATIVE])
                        ->defaultValue(Model::ID_METHOD_NATIVE)
                    ->end()
                    ->scalarNode('namespace')->end()
                    ->booleanNode('activeRecord')->defaultFalse()->end()
                    ->booleanNode('identifierQuoting')->end()
                    ->scalarNode('defaultStringFormat')->end()
                    ->booleanNode('heavyIndexing')->defaultFalse()->end()
                    ->scalarNode('baseClass')->end()
                    ->scalarNode('schema')->end()
                    ->arrayNode('external-schemas')
                        ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                                ->scalarNode('filename')->end()
                                ->booleanNode('referenceOnly')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('behaviors')
                        ->beforeNormalization()
                            ->always(function($behaviors) {
                                foreach ($behaviors as $key => $behavior) {
                                    if (!isset($behavior['id'])) {
                                        $behaviors[$key]['id'] = $behavior['name'];
                                    }
                                }

                                return $behaviors;
                            })
                        ->end()
                        ->useAttributeAsKey('id')
                        ->arrayPrototype()
                            ->fixXmlConfig('parameter')
                            ->children()
                                ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                ->arrayNode('parameters')
                                        ->useAttributeAsKey('name')
                                        ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('name')->end()
                                            ->scalarNode('value')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('vendor')
                        ->fixXmlConfig('parameter')
                        ->children()
                            ->enumNode('type')->values(['mysql', 'MYSQL', 'oracle', 'ORACLE', 'pgsql', 'PGSQL'])->end()
                            ->arrayNode('parameters')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('value')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('entities')
                        ->requiresAtLeastOneElement()
                        ->arrayPrototype()
                            ->addDefaultsIfNotSet()
                            ->fixXmlConfig('field')
                            ->fixXmlConfig('behavior')
                            ->fixXmlConfig('relation')
                            ->fixXmlConfig('index', 'indices')
                            ->fixXmlConfig('unique', 'uniques')
                            ->children()
                                ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->beforeNormalization()
                                        ->always(function($name) {
                                            return NamingTool::toStudlyCase($name);
                                        })
                                    ->end()
                                ->end()
                                ->scalarNode('tableName')->end()
                                ->enumNode('idMethod')
                                    ->values(['native', 'autoincrement', 'sequence', 'none', null])
                                    ->defaultNull()
                                ->end()
                                ->booleanNode('skipSql')->defaultFalse()->end()
                                ->booleanNode('readOnly')->defaultFalse()->end()
                                ->booleanNode('abstract')->defaultFalse()->end()
                                ->booleanNode('isCrossRef')->defaultFalse()->end()
                                ->scalarNode('schema')->end()
                                ->scalarNode('namespace')->end()
                                ->booleanNode('identifierQuoting')->end()
                                ->scalarNode('description')->end()
                                ->booleanNode('activeRecord')->end()
                                ->booleanNode('reloadOnInsert')->defaultFalse()->end()
                                ->booleanNode('reloadOnUpdate')->defaultFalse()->end()
                                ->booleanNode('allowPkInsert')->defaultFalse()->end()
                                ->booleanNode('heavyIndexing')->end()
                                ->scalarNode('defaultStringFormat')->end()
                                ->arrayNode('fields')
                                    ->requiresAtLeastOneElement()
                                    ->fixXmlconfig('inheritance')
                                    ->arrayPrototype()
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                            ->booleanNode('primaryKey')->defaultFalse()->end()
                                            ->booleanNode('required')->defaultFalse()->end()
                                            ->enumNode('type')
                                                ->beforeNormalization()->always(function ($variable) {
                                                    return strtoupper($variable);
                                                })
                                                ->end()
                                                ->values(['BIT', 'TINYINT', 'SMALLINT', 'INTEGER', 'BIGINT', 'FLOAT',
                                                    'REAL', 'NUMERIC', 'DECIMAL', 'CHAR', 'VARCHAR', 'LONGVARCHAR',
                                                    'DATE', 'TIME', 'TIMESTAMP', 'BINARY', 'VARBINARY', 'LONGVARBINARY',
                                                    'NULL', 'OTHER', 'PHP_OBJECT', 'DISTINCT', 'STRUCT', 'ARRAY',
                                                    'BLOB', 'CLOB', 'REF', 'BOOLEANINT', 'BOOLEANCHAR', 'DOUBLE',
                                                    'BOOLEAN', 'OBJECT', 'ENUM'
                                                ])
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                                ->defaultValue('VARCHAR')
                                            ->end()
                                            ->scalarNode('phpType')->end()
                                            ->scalarNode('sqlType')->end()
                                            ->integerNode('size')->end()
                                            ->integerNode('scale')->end()
                                            ->scalarNode('default')->end()
                                            ->scalarNode('defaultValue')->end()
                                            ->scalarNode('defaultExpr')->end()
                                            ->booleanNode('autoIncrement')->defaultFalse()->end()
                                            ->enumNode('inheritance')->values(['single', 'none'])->defaultValue('none')->end()
                                            ->scalarNode('description')->end()
                                            ->booleanNode('lazyLoad')->defaultFalse()->end()
                                            ->booleanNode('primaryString')->defaultFalse()->end()
                                            ->scalarNode('valueSet')->end()
                                            ->arrayNode('inheritances')
                                                ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode('key')->isRequired()->end()
                                                        ->scalarNode('class')->isRequired()->end()
                                                        ->scalarNode('extends')->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('vendor')
                                                ->fixXmlConfig('parameter')
                                                ->children()
                                                    ->enumNode('type')
                                                        ->values(['mysql', 'MYSQL', 'oracle', 'ORACLE', 'pgsql', 'PGSQL'])
                                                    ->end()
                                                    ->arrayNode('parameters')
                                                        ->arrayPrototype()
                                                            ->children()
                                                                ->scalarNode('name')->end()
                                                                ->scalarNode('value')->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('relations')
                                    ->arrayPrototype()
                                        ->fixXmlConfig('reference')
                                        ->children()
                                            ->scalarNode('target')
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                                ->beforeNormalization()
                                                    ->always(function($name) {
                                                        return NamingTool::toStudlyCase($name);
                                                        })
                                                ->end()
                                            ->end()
                                            ->scalarNode('field')->end()
                                            ->scalarNode('name')->end()
                                            ->scalarNode('refField')->end()
                                            ->scalarNode('refName')->end()
                                            ->scalarNode('foreignSchema')->end()
                                            ->enumNode('onUpdate')
                                                ->beforeNormalization()->always(function ($variable) {
                                                    return strtoupper($variable);
                                                })
                                                ->end()
                                                ->values(['CASCADE', 'SETNULL', 'RESTRICT', 'NONE'])
                                            ->end()
                                            ->enumNode('onDelete')
                                                ->beforeNormalization()->always(function ($variable) {
                                                    return strtoupper($variable);
                                                })
                                                ->end()
                                                ->values(['CASCADE', 'SETNULL', 'RESTRICT', 'NONE'])
                                            ->end()
                                            ->enumNode('defaultJoin')
                                                ->beforeNormalization()->always(function ($variable) {
                                                    return strtoupper($variable);
                                                })
                                                ->end()
                                                ->values(['INNER JOIN', 'LEFT JOIN'])
                                            ->end()
                                            ->booleanNode('skipSql')->defaultFalse()->end()
                                            ->arrayNode('references')
                                                ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode('local')->isRequired()->cannotBeEmpty()->end()
                                                        ->scalarNode('foreign')->isRequired()->cannotBeEmpty()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('vendor')
                                                ->fixXmlConfig('parameter')
                                                ->children()
                                                    ->enumNode('type')
                                                        ->values(['mysql', 'MYSQL', 'oracle', 'ORACLE', 'pgsql', 'PGSQL'])
                                                    ->end()
                                                    ->arrayNode('parameters')
                                                        ->arrayPrototype()
                                                            ->children()
                                                                ->scalarNode('name')->end()
                                                                ->scalarNode('value')->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('indices')
                                    ->arrayPrototype()
                                        ->fixXmlConfig('index-field', 'index-fields')
                                        ->normalizeKeys(false)
                                        ->children()
                                            ->scalarNode('name')->end()
                                            ->arrayNode('index-fields')
                                                ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode('name')->isRequired()->end()
                                                        ->integerNode('size')->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('vendor')
                                                ->fixXmlConfig('parameter')
                                                ->children()
                                                    ->enumNode('type')
                                                        ->values(['mysql', 'MYSQL', 'oracle', 'ORACLE', 'pgsql', 'PGSQL'])
                                                    ->end()
                                                    ->arrayNode('parameters')
                                                        ->arrayPrototype()
                                                            ->children()
                                                                ->scalarNode('name')->end()
                                                                ->scalarNode('value')->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('uniques')
                                    ->arrayPrototype()
                                        ->fixXmlConfig('unique-field', 'unique-fields')
                                        ->normalizeKeys(false)
                                        ->children()
                                            ->scalarNode('name')->end()
                                            ->arrayNode('unique-fields')
                                                ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode('name')->isRequired()->end()
                                                        ->integerNode('size')->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('vendor')
                                                ->fixXmlConfig('parameter')
                                                ->children()
                                                    ->enumNode('type')
                                                        ->values(['mysql', 'MYSQL', 'oracle', 'ORACLE', 'pgsql', 'PGSQL'])
                                                    ->end()
                                                    ->arrayNode('parameters')
                                                        ->arrayPrototype()
                                                            ->children()
                                                                ->scalarNode('name')->end()
                                                                ->scalarNode('value')->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('behaviors')
                                    ->beforeNormalization()
                                        ->always(function($behaviors) {
                                            foreach ($behaviors as $key => $behavior) {
                                                if (!isset($behavior['id'])) {
                                                    $behaviors[$key]['id'] = $behavior['name'];
                                                }
                                            }

                                            return $behaviors;
                                        })
                                    ->end()
                                    ->useAttributeAsKey('id')
                                    ->arrayPrototype()
                                        ->fixXmlConfig('parameter')
                                        ->children()
                                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                            ->arrayNode('parameters')
                                                    ->useAttributeAsKey('name')
                                                    ->arrayPrototype()
                                                    ->children()
                                                        ->scalarNode('name')->end()
                                                        ->scalarNode('value')->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('vendor')
                                    ->fixXmlConfig('parameter')
                                    ->children()
                                        ->enumNode('type')->values(['mysql', 'MYSQL','oracle', 'ORACLE', 'pgsql', 'PGSQL'])->end()
                                        ->arrayNode('parameters')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('name')->end()
                                                    ->scalarNode('value')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('id_method_parameter')
                                    ->children()
                                        ->scalarNode('value')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}