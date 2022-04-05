<?php

declare(strict_types=1);

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;

class ModuleStatusTest extends TestCase
{
    public function testIsModuleValid()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isLoaded')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isValid($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleNotValid()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isLoaded')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isValid($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleLoadable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isLoadable')->willReturn(true);
        $stubModule->method('isRemote')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isLoadable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    //TODO:
    public function testIsModuleNotLoadable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isLoadable')->willReturn(false);
        $stubModule->method('isRemote')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isLoadable($stubModule);

        //Assert
        $this->assertTrue($result);
        //$this->assertFalse($result); //TODO - Sollte nicht fale richtig sein?
    }

    public function testIsModuleCompatibleLoadable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(true);
        $stubModule->method('isLoadable')->willReturn(true);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isLoaded')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatibleLoadable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotCompatibleLoadable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(false);
        $stubModule->method('isLoadable')->willReturn(true);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isLoaded')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatibleLoadable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleIncompatibleLoadable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(false);
        $stubModule->method('isLoadable')->willReturn(true);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isLoaded')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isIncompatibleLoadebale($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotIncompatibleLoadable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(true);
        $stubModule->method('isLoadable')->willReturn(true);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isLoaded')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isIncompatibleLoadebale($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleRepairable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('isInstalled')->willReturn(true);
        $stubModule->method('isChanged')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isRepairable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotRepairable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('isInstalled')->willReturn(true);
        $stubModule->method('isChanged')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isRepairable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleUninstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('isInstalled')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isUninstallable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotUninstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('isInstalled')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isUninstallable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn(null);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isInstallable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn(new Module());

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isInstallable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleCompatibleInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn(null);
        $stubModule->method('isCompatible')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatibleInstallable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotCompatibleInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn(null);
        $stubModule->method('isCompatible')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatibleInstallable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleCompatibleLoadableAndInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isLoadable')->willReturn(true);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isCompatible')->willReturn(true);

        $stubModule->method('isLoaded')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(false);
        $stubModule->method('getInstalledVersion')->willReturn(null);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatibleLoadableAndInstallable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotCompatibleLoadableAndInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isLoadable')->willReturn(false);
        $stubModule->method('isRemote')->willReturn(true);
        $stubModule->method('isCompatible')->willReturn(true);

        $stubModule->method('isLoaded')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(false);
        $stubModule->method('getInstalledVersion')->willReturn(null);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatibleLoadableAndInstallable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleIncompatibleInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn(null);
        $stubModule->method('isCompatible')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isIncompatibleInstallable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotIncompatibleInstallable()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isLoaded')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn(null);
        $stubModule->method('isCompatible')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isIncompatibleInstallable($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleCompatible()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatible($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotCompatible()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isCompatible($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleIncompatible()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(false);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isIncompatible($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotIncompatible()
    {
        // Arrage
        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isCompatible')->willReturn(true);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isIncompatible($stubModule);

        //Assert
        $this->assertFalse($result);
    }

    public function testIsModuleUpdateable()
    {
        // Arrage
        /** @var MockObject $stubNewestModule */
        $stubNewestModule = $this->createMock(Module::class);
        $stubNewestModule->method('getVersion')->willReturn('1.0.1');
        $stubNewestModule->method('isLoadable')->willReturn(true);

        /** @var MockObject $stubInstalledModule */
        $stubInstalledModule = $this->createMock(Module::class);
        $stubInstalledModule->method('getVersion')->willReturn('1.0.0');

        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn($stubInstalledModule);
        $stubModule->method('getNewestVersion')->willReturn($stubNewestModule);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isUpdatable($stubModule);

        //Assert
        $this->assertTrue($result);
    }

    public function testIsModuleNotUpdateable()
    {
        // Arrage
        /** @var MockObject $stubNewestModule */
        $stubNewestModule = $this->createMock(Module::class);
        $stubNewestModule->method('getVersion')->willReturn('1.0.0');
        $stubNewestModule->method('isLoadable')->willReturn(true);

        /** @var MockObject $stubInstalledModule */
        $stubInstalledModule = $this->createMock(Module::class);
        $stubInstalledModule->method('getVersion')->willReturn('1.0.0');

        /** @var MockObject $stubModule */
        $stubModule = $this->createMock(Module::class);
        $stubModule->method('isRemote')->willReturn(false);
        $stubModule->method('isInstalled')->willReturn(true);
        $stubModule->method('getInstalledVersion')->willReturn($stubInstalledModule);
        $stubModule->method('getNewestVersion')->willReturn($stubNewestModule);

        //Act
        /** @var Module $stubModule */
        $result = ModuleStatus::isUpdatable($stubModule);

        //Assert
        $this->assertFalse($result);
    }
}
