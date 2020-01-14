<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */

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
 *
 * @package    sample-api
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class <?= $className ?> extends Migration
{
    public function safeUp()
    {
        //
    }

    public function safeDown(): bool
    {
        echo '<?= $className ?> no need reverted.' . PHP_EOL;

        return true;
    }
}
