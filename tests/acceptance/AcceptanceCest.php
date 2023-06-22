<?php

class AcceptanceCest
{
    private $sessionCookie;

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

    // public function _before(AcceptanceTester $I)
    // {
    // }

    public function signInSuccessfully(AcceptanceTester $I)
    {
        $this->_login($I);
    }

    /**
     * @after signInSuccessfully
     */
    public function seeAllModules(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/');
        $I->see('Alle Module');

        $I->seeNumberOfElements(".module-serach-box", [70,200]);

        // Remote Module
        $I->see('Sprachpaket serbisch');

        // Local Module
        $I->see('Composer Autoload');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeModuleDetails(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?action=moduleInfo&archiveName=grandeljay/ups&version=0.2.4');
        $I->see('grandeljay/ups');

        $I->see('Version        0.2.4');
        $I->see('Kompatibel mit Modified        2.0.7.2');
        $I->see('Kompatibel mit PHP        ^8.0.0');
        $I->see('Kompatibel mit MMLC        ^1.21.0');
        $I->see('Benötigt        composer/autoload: ^1.3.0');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeLoadedModules(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?filterModules=loaded');
        $I->see('Geladene Module');

        $I->seeNumberOfElements(".module-serach-box", [1,200]);

        // Local Module
        $I->see('Composer Autoload');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeInstalledModules(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?filterModules=installed');
        $I->see('Installierte Module');

        $I->seeNumberOfElements(".module-serach-box", [1,200]);

        // Local Module
        $I->see('Composer Autoload');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeUpdatableModules(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?filterModules=updatable');
        $I->see('Aktualisierbare Module');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeChangedModules(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?filterModules=changed');
        $I->see('Geänderte Module');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeNotLoadedModules(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?filterModules=notloaded');
        $I->see('Nicht geladene Module');

        $I->seeNumberOfElements(".module-serach-box", [10,200]);
    }

    /**
     * @after signInSuccessfully
     */
    public function seeSupportPage(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?action=support');
        $I->see('Hilfe & Support');
        $I->see('Modul-Entwickler werden');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeSystemPage(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?action=selfUpdate');
        $I->see('MMLC - Modified Module Loader Client');
        $I->see('AccessToken:');
        $I->see('Domain:');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeSettingPage(AcceptanceTester $I)
    {
        $this->_login($I);

        $I->amOnPage('/?action=settings');
        $I->see('Einstellungen');
        $I->see('Allgemein');
        $I->see('AccessToken');

        $I->see('Benutzer');
        $I->see('Benutzername');
        $I->see('Password');

        $I->see('Benutzer');
        $I->see('Benutzername');
        $I->see('Password');

        $I->see('Erweitert');
        $I->see('Module Pfad');
        $I->see('Installationsmodus');
    }
}
