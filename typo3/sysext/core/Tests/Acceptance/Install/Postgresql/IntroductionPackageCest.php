<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Acceptance\Install\Postgresql;

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

use Codeception\Scenario;
use TYPO3\CMS\Core\Tests\Acceptance\Support\InstallTester;

/**
 * Click through installer, go to backend, install introduction package
 */
class IntroductionPackageCest
{
    /**
     * @param InstallTester $I
     * @param Scenario $scenario
     * @env postgresql
     */
    public function installTypo3OnPgSql(InstallTester $I, Scenario $scenario)
    {
        // Calling frontend redirects to installer
        $I->amOnPage('/');

        // EnvironmentAndFolders step
        $I->waitForText('Installing TYPO3');
        $I->waitForText('No problems detected, continue with installation');
        $I->click('No problems detected, continue with installation');

        // DatabaseConnection step
        $I->waitForText('Select database');
        $I->selectOption('#t3js-connect-database-driver', 'Manually configured PostgreSQL connection');
        $I->fillField('#t3-install-step-postgresManualConfiguration-username', $scenario->current('typo3InstallPostgresqlDatabaseUsername'));
        // password intentionally not filled. Postgres authenticates with the shell user.
        $I->fillField('#t3-install-step-postgresManualConfiguration-database', $scenario->current('typo3InstallPostgresqlDatabaseName'));
        $I->click('Continue');

        // DatabaseData step
        $I->waitForText('Create Administrative User / Specify Site Name');
        $I->fillField('#username', 'admin');
        $I->fillField('#password', 'password');
        $I->click('Continue');

        // DefaultConfiguration step - load distributions
        $I->waitForText('Installation Complete');
        $I->click('#load-distributions');
        $I->click('Open the TYPO3 Backend');

        // Verify backend login successful
        $I->waitForElement('#t3-username');
        $I->fillField('#t3-username', 'admin');
        $I->fillField('#t3-password', 'password');
        $I->click('#t3-login-submit-section > button');
        $I->waitForElement('.nav', 30);
        $I->waitForElement('.scaffold-content iframe', 30);
        $I->seeCookie('be_lastLoginProvider');
        $I->seeCookie('be_typo_user');

        // Loading might take some time
        $I->wait(10);
        $I->switchToIFrame('list_frame');
        $I->waitForText('Get preconfigured distribution', 30);
        $I->click('.t3-button-action-installdistribution');
        $I->waitForText('You successfully installed the distribution \'introduction\'', 240);

        // Verify default frontend is rendered
        $I->amOnPage('/');
        $I->waitForText('Let us introduce you to TYPO3', 30);
        $I->waitForText('Make it your own');

        // Verify link
        $I->click('[title="Features"]');
        $I->waitForText('Feature Complete Out-of-the-box', 30);
    }
}
