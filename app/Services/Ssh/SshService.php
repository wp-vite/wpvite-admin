<?php

namespace App\Services\Ssh;

use Exception;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class SshService
{
    protected string $privateKeyPath;
    protected string $connection;
    protected ?string $targetUser = null;

    /**
     * Create SSH connection
     * @param string $host
     * @param string $username
     * If not provided it will use default from the ENV variable (SSH_USERNAME)
     *
     * @throws \Exception
     * @return \App\Services\Ssh\SshService
     */
    public static function create(string $host, string $username = null): self
    {
        $username   = $username ?? Config::get('wpvite.ssh.username');
        if(!$username) {
            throw new Exception("SSH Connection failed: Username required.");
        }

        $connection = sprintf('%s@%s', $username, $host);
        return new self($connection);
    }

    protected function __construct(string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Use Private Key file for authentication
     * @param string $privateKeyPath
     * If not provided it will use default from the ENV variable (SSH_PRIVATE_KEY_PATH)
     *
     * @throws \RuntimeException
     * @return \App\Services\Ssh\SshService
     */
    public function usePrivateKey(string $privateKeyPath = null): self
    {
        if(!$privateKeyPath) {
            $privateKeyPath = Config::get('wpvite.ssh.private_key_path');
        }

        $resolvedPath = realpath($privateKeyPath);

        if ($resolvedPath === false) {
            throw new RuntimeException('Private key file not found. Please check the path.');
        }

        $this->privateKeyPath = $resolvedPath;
        return $this;
    }

    /**
     * Execute the commands on behalf of this target user
     * @param string $targetUser
     * @return \App\Services\Ssh\SshService
     */
    public function asUser(string $targetUser): self
    {
        $this->targetUser = $targetUser;
        return $this;
    }

    /**
     * Commands to execute
     * @param array $commands
     * @throws \RuntimeException
     * @return object
     *  ->isSuccessful() true|false
     *  ->getOutput() string
     *  ->getErrorOutput() string
     */
    public function execute(array $commands): object
    {
        // Securely join commands
        $command = implode(' && ', array_map('escapeshellcmd', $commands));

        if ($this->targetUser) {
            $command = sprintf(
                "sudo -u %s bash -c '%s'",
                $this->targetUser,
                $command
            );
        }

        $sshCommand = sprintf(
            'ssh -i "%s" %s -o StrictHostKeyChecking=no %s',
            $this->privateKeyPath,
            $this->connection,
            escapeshellarg($command)
        );
        // dd($sshCommand);

        $process = proc_open($sshCommand, [
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w'], // STDERR
        ], $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to initiate SSH process.');
        }

        $output = stream_get_contents($pipes[1]); // Get STDOUT
        $errorOutput = stream_get_contents($pipes[2]); // Get STDERR

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        return new class($returnCode, $output, $errorOutput) {
            public bool $success;
            public string $output;
            public string $errorOutput;

            public function __construct(int $returnCode, string $output, string $errorOutput)
            {
                $this->success = ($returnCode === 0);
                $this->output = $output;
                $this->errorOutput = $errorOutput;
            }

            public function isSuccessful(): bool
            {
                return $this->success;
            }

            public function getOutput(): string
            {
                return $this->output;
            }

            public function getErrorOutput(): string
            {
                return $this->errorOutput;
            }
        };
    }
}
