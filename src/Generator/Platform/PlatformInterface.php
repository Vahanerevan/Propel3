<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

declare(strict_types=1);

namespace Propel\Generator\Platform;

use Propel\Generator\Config\GeneratorConfigInterface;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\Field;
use Propel\Generator\Model\Domain;
use Propel\Generator\Model\Entity;
use Propel\Generator\Platform\Builder\Repository;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Interface for RDBMS platform specific behaviour.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Propel)
 * @author Martin Poeschl <mpoeschl@marmot.at> (Torque)
 */
interface PlatformInterface
{

    /**
     * Constant for auto-increment id method.
     */
    const IDENTITY = "identity";

    /**
     * Constant for sequence id method.
     */
    const SEQUENCE = "sequence";

    /**
     * Constant for serial id method (postgresql).
     */
    const SERIAL = "serial";

    /**
     * Sets a database connection to use (for quoting, etc.).
     * @param ConnectionInterface $con The database connection to use in this Platform class.
     */
    public function setConnection(ConnectionInterface $con = null);

    /**
     * Returns the database connection to use for this Platform class.
     * @return ConnectionInterface The database connection or NULL if none has been set.
     */
    public function getConnection(): ?ConnectionInterface;

    /**
     * Finalizes $entity definitions. For example for SQL platforms you need to make sure
     * relations have the correct references applied.
     *
     * @param Database $database
     */
    public function finalizeDefinition(Database $database);

    /**
     * @param Entity $entity
     *
     * @return Repository
     */
    public function getRepositoryBuilder(Entity $entity): Repository;

    /**
     * Sets the GeneratorConfigInterface which contains any generator build properties.
     *
     * @param GeneratorConfigInterface $generatorConfig
     */
    public function setGeneratorConfig(GeneratorConfigInterface $generatorConfig);

    /**
     * Returns the short name of the database type that this platform represents.
     * For example MysqlPlatform->getDatabaseType() returns 'mysql'.
     *
     * @return string
     */
    public function getDatabaseType(): string;

    /**
     * Returns the native IdMethod (sequence|identity)
     *
     * @return string The native IdMethod (PlatformInterface:IDENTITY, PlatformInterface::SEQUENCE).
     */
    public function getNativeIdMethod(): string;

    /**
     * Returns the max field length supported by the db.
     *
     * @return int The max field length
     */
    public function getMaxFieldNameLength(): int;

    /**
     * Returns the db specific domain for a propelType.
     *
     * @param  string $propelType the Propel type name.
     * @return Domain The db specific domain.
     */
    public function getDomainForType(string $propelType): Domain;

    /**
     * @return string The RDBMS-specific SQL fragment for <code>NULL</code>
     */
    public function getNullString(): string;

    /**
     * @return string The RDBMS-specific SQL fragment for <code>NOT NULL</code>.
     */
    public function getNotNullString(): string;

    /**
     * @return string The RDBMS-specific SQL fragment for autoincrement.
     */
    public function getAutoIncrement(): string;

    /**
     * Returns the DDL SQL for a Field object.
     * @return string
     */
    public function getFieldDDL(Field $col): string;

    /**
     * Returns the SQL for the default value of a Field object.
     * @return string
     */
    public function getFieldDefaultValueDDL(Field $col): string;

    /**
     * Creates a delimiter-delimited string list of field names, quoted using quoteIdentifier().
     * @example
     * <code>
     * echo $platform->getFieldListDDL(array('foo', 'bar');
     * // '"foo","bar"'
     * </code>
     * @param Field[]|string[] $fields
     * @param string            $delimiter The delimiter to use in separating the field names.
     *
     * @return string
     */
    public function getFieldListDDL($fields, string $delimiter = ','): string;

    /**
     * Returns the SQL for the primary key of a Entity object
     * @return string
     */
    public function getPrimaryKeyDDL(Entity $entity): string;

    /**
     * Returns if the RDBMS-specific SQL type has a size attribute.
     *
     * @param  string  $sqlType the SQL type
     * @return boolean True if the type has a size attribute
     */
    public function hasSize(string $sqlType): bool;

    /**
     * Returns if the RDBMS-specific SQL type has a scale attribute.
     *
     * @param  string  $sqlType the SQL type
     * @return boolean True if the type has a scale attribute
     */
    public function hasScale(string $sqlType): bool;

    /**
     * Quote and escape needed characters in the string for underlying RDBMS.
     * @param  string $text
     * @return string
     */
    public function quote(string $text): string;

    /**
     * Quotes a identifier.
     *
     * @param string $text
     * @return string
     */
    public function doQuoting(string $text): string;

    /**
     * Whether RDBMS supports native index sizes.
     * @return boolean
     */
    public function supportsIndexSize(): bool;

    /**
     * Whether RDBMS supports native ON DELETE triggers (e.g. ON DELETE CASCADE).
     * @return boolean
     */
    public function supportsNativeDeleteTrigger(): bool;

    /**
     * Whether RDBMS supports INSERT null values in autoincremented primary keys
     * @return boolean
     */
    public function supportsInsertNullPk(): bool;

    /**
     * Whether RDBMS supports native schemas for entity layout.
     * @return boolean
     */
    public function supportsSchemas(): bool;

    /**
     * Whether RDBMS supports migrations.
     * @return boolean
     */
    public function supportsMigrations(): bool;

    /**
     * Whether RDBMS supports VARCHAR without explicit size
     * @return boolean
     */
    public function supportsVarcharWithoutSize(): bool;

    /**
     * Returns the boolean value for the RDBMS.
     *
     * This value should match the boolean value that is set
     * when using Propel's PreparedStatement::setBoolean().
     *
     * This function is used to set default field values when building
     * SQL.
     *
     * @param  mixed $tf A boolean or string representation of boolean ('y', 'true').
     * @return mixed
     */
    public function getBooleanString($tf);

    /**
     * Whether the underlying PDO driver for this platform returns BLOB fields as streams (instead of strings).
     * @return boolean
     */
    public function hasStreamBlobImpl(): bool;

    /**
     * Gets the preferred timestamp formatter for setting date/time values.
     * @return string
     */
    public function getTimestampFormatter(): string;

    /**
     * Gets the preferred date formatter for setting time values.
     * @return string
     */
    public function getDateFormatter(): string;

    /**
     * Gets the preferred time formatter for setting time values.
     * @return string
     */
    public function getTimeFormatter(): string;

    /**
     * @return string
     */
    public function getSchemaDelimiter(): string;

    /**
     * Normalizes a entity for the current platform. Very important for the EntityComparator to not
     * generate useless diffs.
     * Useful for checking needed definitions/structures. E.g. Unique Indexes for Relation fields,
     * which the most Platforms requires but which is not always explicitly defined in the entity model.
     *
     * @param Entity $entity The entity object which gets modified.
     */
    public function normalizeEntity(Entity $entity);


    /**
     * Get the PHP snippet for binding a value to a field.
     * Warning: duplicates logic from AdapterInterface::bindValue().
     * Any code modification here must be ported there.
     */
    public function getFieldBindingPHP(Field $field, $identifier, $fieldValueAccessor, $tab = "            ");

    /**
     * @return boolean
     */
    public function isIdentifierQuotingEnabled(): bool;

    /**
     * @param boolean $enabled
     */
    public function setIdentifierQuoting(bool $enabled);
}
