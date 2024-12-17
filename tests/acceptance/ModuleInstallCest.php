<?php

class ModuleInstallerCest
{
    private $sessionCookie;

    public function _before(AcceptanceTester $I)
    {
        $this->_login($I);
        $this->_setModuleTestDir($I);
        $this->_cleanUp($I);
    }

    public function _after(AcceptanceTester $I)
    {
        $this->_login($I);
        $this->_cleanUp($I);
        $this->_setModuleWorkingDir($I);
    }

    public function testModuleInstaller(AcceptanceTester $I)
    {
        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/stripe');
        $I->see('Stripe');
        $I->see('Download & Installieren');
        $I->click('Download & Installieren');
        $I->see('Deinstallieren');
        $I->see('installiert');

        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/modified-std-module');
        $I->see('Deinstallieren');
        $I->see('installiert');

        $I->amOnPage('/?action=moduleInfo&archiveName=composer/autoload');
        $I->see('Deinstallieren');
        $I->see('installiert');

        // canSeeInstalledModules
        $I->amOnPage('/?filterModules=installed');
        $I->see('Installierte Module');
        $I->seeNumberOfElements(".module-serach-box", [1,200]);
        $I->see('Composer Autoload');
        $I->see('tandard Modul für Modified');
        $I->see('Stripe');

        // canUninstallModules
        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/stripe');
        $I->see('Stripe');
        $I->tryToClick('Deinstallieren');

        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/modified-std-module');
        $I->see('Standard Modul für Modified');
        $I->tryToClick('Deinstallieren');

        $I->amOnPage('/?action=moduleInfo&archiveName=composer/autoload');
        $I->see('Composer Autoload');
        $I->tryToClick('Deinstallieren');

        // canDeleteModules
        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/stripe');
        $I->see('Stripe');
        $I->tryToClick('Löschen');

        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/modified-std-module');
        $I->see('Standard Modul für Modified');
        $I->tryToClick('Löschen');

        $I->amOnPage('/?action=moduleInfo&archiveName=composer/autoload');
        $I->see('Composer Autoload');
        $I->tryToClick('Löschen');
    }

    public function _login(AcceptanceTester $I)
    {
        // set session cookie
        if ($this->sessionCookie) {
            $I->setCookie('PHPSESSID', $this->sessionCookie);
            return;
        }

        // logging in
        $I->amOnPage('/?action=signIn');
        $I->fillField('username', 'root');
        $I->fillField('password', 'root');
        $I->click('Anmelden');

        // saving session cookie
        $this->sessionCookie = $I->grabCookie('PHPSESSID');
    }

    public function _setModuleTestDir(AcceptanceTester $I)
    {
        $I->amOnPage('/?action=settings');
        $I->fillField('modulesLocalDir', 'ModulesTest');
        $I->click('#v-pills-advanced button');
    }

    public function _setModuleWorkingDir(AcceptanceTester $I)
    {
        $I->amOnPage('/?action=settings');
        $I->fillField('modulesLocalDir', 'Modules');
        $I->click('#v-pills-advanced button');
    }

    public function _cleanUp(AcceptanceTester $I)
    {
        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/stripe');
        $I->see('Stripe');
        $I->tryToClick('Deinstallieren');
        $I->tryToClick('Löschen');

        $I->amOnPage('/?action=moduleInfo&archiveName=robinthehood/modified-std-module');
        $I->see('Standard Modul für Modified');
        $I->tryToClick('Deinstallieren');
        $I->tryToClick('Löschen');

        $I->amOnPage('/?action=moduleInfo&archiveName=composer/autoload');
        $I->see('Composer Autoload');
        $I->tryToClick('Deinstallieren');
        $I->tryToClick('Löschen');
    }
}
