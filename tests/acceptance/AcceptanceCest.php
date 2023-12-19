<?php

class AcceptanceCest
{
    private $sessionCookie;

    public function _before(AcceptanceTester $I)
    {
        $this->_login($I);
    }

    public function _after(AcceptanceTester $I)
    {
        $this->_login($I);
    }

    public function signInSuccessfully(AcceptanceTester $I)
    {
        $this->_login($I);
    }

    /**
     * @after signInSuccessfully
     */
    public function seeAllModules(AcceptanceTester $I)
    {
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
    public function seeLoadedModules(AcceptanceTester $I)
    {
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
        $I->amOnPage('/?filterModules=installed');
        $I->see('Installierte Module');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeUpdatableModules(AcceptanceTester $I)
    {
        $I->amOnPage('/?filterModules=updatable');
        $I->see('Aktualisierbare Module');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeChangedModules(AcceptanceTester $I)
    {
        $I->amOnPage('/?filterModules=changed');
        $I->see('Geänderte Module');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeNotLoadedModules(AcceptanceTester $I)
    {
        $I->amOnPage('/?filterModules=notloaded');
        $I->see('Nicht geladene Module');

        $I->seeNumberOfElements(".module-serach-box", [10,200]);
    }

    /**
     * @after signInSuccessfully
     */
    public function seeSupportPage(AcceptanceTester $I)
    {
        $I->amOnPage('/?action=support');
        $I->see('Hilfe & Support');
        $I->see('Modul-Entwickler werden');
    }

    /**
     * @after signInSuccessfully
     */
    public function seeSystemPage(AcceptanceTester $I)
    {
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
}
