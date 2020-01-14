<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $this yii\console\controllers\MigrateController  */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */
/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $foreignKeys array the foreign keys */

echo "<?php\n\n";
?>
/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);
<?php
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use yii\db\Migration;

/**
 * Миграция ...
<?= $this->render('@yii/views/_foreignTables', [
    'foreignKeys' => $foreignKeys,
]) ?>
 *
 * @package    sample-api
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class <?= $className ?> extends Migration
{
    public function safeUp()
    {
<?= $this->render('@yii/views/_createTable', [
    'table' => $table,
    'fields' => $fields,
    'foreignKeys' => $foreignKeys,
])
?>
    }

    public function safeDown(): bool
    {
<?= $this->render('@yii/views/_dropTable', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
])
?>

        return true;
    }
}
