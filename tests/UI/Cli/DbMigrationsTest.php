<?php

declare(strict_types=1);

namespace Waglpz\Cli\Tests\UI\Cli;

use Aura\Sql\ExtendedPdoInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Waglpz\Cli\UI\Cli\CliError;
use Waglpz\Cli\UI\Cli\DbMigrations;

class DbMigrationsTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @test
     */
    public function noMigrationsForExecution(): void
    {
        $dirName = '/tmp/' . \uniqid();
        \mkdir($dirName, 0777, true);
        $options    = [
            'migrations' => $dirName,
            'usage'      => [],
        ];
        $connection = $this->createMock(ExtendedPdoInterface::class);
        $connection->expects(self::never())->method('beginTransaction');
        $connection->expects(self::once())->method('fetchCol')->willReturn([]);
        $connection->expects(self::never())->method('fetchAffected');
        $connection->expects(self::never())->method('exec');
        $connection->expects(self::never())->method('commit');
        $connection->expects(self::never())->method('rollBack');

        $command = new DbMigrations($connection, $options);

        $_SERVER['argv'][2] = 'migrate';

        $this->expectOutputString('Nothing to do, no new migrations to execute.' . \PHP_EOL);
        $command();
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function check(): void
    {
        $options    = [
            'migrations' => '',
            'usage'      => [],
        ];
        $connection = $this->createMock(ExtendedPdoInterface::class);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Migration directory not writeable or does not exists "".');
        (new DbMigrations($connection, $options))();
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function executeMigrations(): void
    {
        $options = [
            'migrations' => __DIR__ . '/migrations-stubs',
            'usage'      => [],
        ];

        $migrations = [1605646638];

        $connection = $this->createMock(ExtendedPdoInterface::class);
        $connection->expects(self::once())->method('beginTransaction');
        $connection->expects(self::once())->method('fetchCol')
                   ->with('SELECT migration FROM __migrations ORDER BY migration')
                   ->willReturn($migrations);
        $connection->expects(self::once())->method('fetchAffected')->willReturn(1);
        $connection->expects(self::once())->method('exec')
                   ->with('INSERT INTO __migrations (migration) VALUES (1605646639)')
                   ->willReturn(1);
        $connection->expects(self::once())->method('inTransaction')->willReturn(true);
        $connection->expects(self::once())->method('commit');
        $connection->expects(self::never())->method('rollBack');

        $command = new DbMigrations($connection, $options);

        $_SERVER['argv'][2] = 'migrate';

        $output = $command()->__toString();
        self::assertSame(
            'Result migrations:
  Affected rows #1
  Applied migrations #1
',
            $output,
        );
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function rollBackMigrations(): void
    {
        $options = [
            'migrations' => __DIR__ . '/migrations-stubs',
            'usage'      => [],
        ];

        $migrations = [1605646638];

        $connection = $this->createMock(ExtendedPdoInterface::class);
        $connection->expects(self::once())->method('beginTransaction');
        $connection->expects(self::once())->method('fetchCol')
                   ->with('SELECT migration FROM __migrations ORDER BY migration')
                   ->willReturn($migrations);
        $connection->expects(self::once())->method('fetchAffected')->willReturn(1);
        $connection->expects(self::once())->method('exec')
                   ->with('INSERT INTO __migrations (migration) VALUES (1605646639)')
                   ->willThrowException(new \Exception('Test Exception message'));
        $connection->expects(self::never())->method('commit');
        $connection->expects(self::once())->method('rollBack');

        $command            = new DbMigrations($connection, $options);
        $_SERVER['argv'][2] = 'migrate';

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('Test Exception message');
        $command();
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function generateNewMigration(): void
    {
        $options    = [
            'migrations' => '/tmp',
            'usage'      => [],
        ];
        $connection = $this->createMock(ExtendedPdoInterface::class);

        $_SERVER['argv'][2] = 'generate';
        (new DbMigrations($connection, $options))();
        self::assertFileExists('/tmp/migration-' . \time() . '-up.sql');
        self::assertFileExists('/tmp/migration-' . \time() . '-down.sql');
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function nochNichtImplementierteMethode(): void
    {
        $options    = [
            'migrations' => '/tmp',
            'usage'      => [],
        ];
        $connection = $this->createMock(ExtendedPdoInterface::class);

        $_SERVER['argv'][2] = 'up';

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "up" not yet implemented.');
        (new DbMigrations($connection, $options))();
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function usageWirdAnzeigt(): void
    {
        $options    = [
            'migrations' => '/tmp',
            'usage'      => [],
        ];
        $connection = $this->createMock(ExtendedPdoInterface::class);

        $_SERVER['argv'][2] = 'wrong';

        $this->expectException(CliError::class);
        $this->expectExceptionMessageMatches('/Usage:/');
        (new DbMigrations($connection, $options))();
    }
}
