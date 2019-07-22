<?php
/**
 * 2019 BJ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    BJ <perso@bdesprez.com>
 *  @copyright 2019 BJ
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace bdesprez\psmodulefwk\ajax;

use Exception;
use stdClass;
use bdesprez\psmodulefwk\MyModule;
use bdesprez\psmodulefwk\TranslationTrait;

abstract class ModuleProcessAjax implements ProcessAjax
{
    use TranslationTrait;

    /**
     * @var MyModule
     */
    protected $module;

    /**
     * @var string
     */
    private $name;

    /**
     * ModuleProcessAjax constructor.
     * @param MyModule $module
     */
    public function __construct(MyModule $module)
    {
        $this->module = $module;
    }

    /**
     * @param bool $capitalized
     * @return string
     */
    public function getName($capitalized = false)
    {
        if ($this->name === null) {
            $this->name = $this->getSimpleName();
        }
        if (!$capitalized) {
            return strtolower(substr($this->name, 0, 1)) . substr($this->name, 1);
        }
        return $this->name;
    }

    /**
     * @return array|stdClass
     * @throws AjaxException
     */
    abstract protected function execute();

    /**
     * @return mixed|void
     */
    public function process()
    {
        $this->getLogger()->logInfo('Trying to process ajax ' . $this->getName(true), 'Ajax');
        try {
            $retour = $this->execute();
            $retour = (array) $retour;
            $retour['success'] = 1;
            echo json_encode($retour);
            exit;
        } catch (Exception $exception) {
            $this->generateJsonErrorForAjax($exception->getMessage());
        }
    }

    /**
     * @param $message
     * @return false|string
     */
    protected function generateJsonErrorForAjax($message)
    {
        echo json_encode(['success' => 0, 'error' => $message]);
        exit;
    }

    /**
     * @return MyModule
     */
    protected function getModule()
    {
        return $this->module;
    }
}
