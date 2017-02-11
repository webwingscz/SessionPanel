<?php

namespace Webwings\Tracy;

use Tracy;

/**
 * Session Nette Debug Panel
 * @author Jiří Dorazil <dorazil@webwings.cz>
 * @author Pavel Železný <info@pavelzelezny.cz>
 */
class SessionPanel implements Tracy\IBarPanel {

    /** @var \Nette\Http\Session $session */
    private $session;

    /** @var array $hiddenSection */
    private $hiddenSections = array();

    /**
     *
     * @param \Nette\Http\Session $session
     */
	public function register(\Nette\Http\Session $session)
	{
        $this->session = $session;
		Tracy\Debugger::getBar()->addPanel($this);
        Tracy\Debugger::getBlueScreen()->addPanel([__CLASS__, 'renderException']);
	}

    /**
     * Add section name in list of hidden
     * @param string $sectionName
     * @return void
     */
    public function hideSection($sectionName) {
        $this->hiddenSections[] = $sectionName;
    }

    /**
     * Return panel ID
     * @return string
     */
    public function getId() {
        return __CLASS__;
    }

    /**
     * Html code for DebugerBar Tab
     * @return string
     */

    public function getTab() {
        return $this->getFileTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tab.latte');
    }

    /**
     * Html code for DebugerBar Panel
     * @return string
     */
    public function getPanel() {
        $params['session'] = $this->session->isStarted() ? $this->session : FALSE;
        $params['sessionMetaStore'] = isset($_SESSION['__NF']['META']) ? $_SESSION['__NF']['META'] : array();
        $params['sessionMaxTime'] = ini_get('session.gc_maxlifetime');
        $params['hiddenSections'] = $this->hiddenSections;
        return $this->getFileTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'panel.latte',$params);
    }

    /**
     * Load template file path with aditional macros and variables
     * @param string $templateFilePath
     * @return String
     * @throws \Nette\FileNotFoundException
     */
    private function getFileTemplate($templateFilePath,$params = []) {
        if (file_exists($templateFilePath)) {
            $latte = new \Latte\Engine;

            $latte->onCompile[] =  function ($latte){
                $set = new \Latte\Macros\MacroSet($latte->getCompiler());
                $set->addMacro('src', NULL, NULL, 'echo \'src="\'.\Nette\Templating\Helpers::dataStream(file_get_contents(%node.word)).\'"\'');
                $set->addMacro('stylesheet', 'echo \'<style type="text/css">\'.file_get_contents(%node.word).\'</style>\'');
                $set->addMacro('dumper', 'echo \Tracy\Dumper::toHtml(%node.word)');
            };

            $params['basePath'] = realpath(__DIR__);
            $template = $latte->renderToString($templateFilePath,$params);
            return $template;

        } else {
            throw new \Nette\FileNotFoundException('Requested template file is not exist.');
        }
    }

}
