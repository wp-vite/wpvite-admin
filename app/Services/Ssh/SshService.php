<?php

namespace App\Services\Ssh;

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Illuminate\Support\Facades\Config;
use Exception;
use RuntimeException;

class SshService
{
    protected SSH2 $ssh;
    protected string $host;
    protected string $username;
    protected string $privateKeyPath;
    protected ?string $targetUser = null;
    protected int $port = 22;

    /**
     * Create SSH connection
     * @param string $host
     * @param string|null $username
     * If not provided, it will use default from ENV variable (SSH_USERNAME)
     *
     * @throws Exception
     * @return SshService
     */
    public static function create(string $host, ?string $username = null): self
    {
        $username = $username ?? Config::get('wpvite.ssh.username');
        if (!$username) {
            throw new Exception("SSH Connection failed: Username required.");
        }

        return new self($host, $username);
    }

    protected function __construct(string $host, string $username)
    {
        $this->host = $host;
        $this->username = $username;
    }

    /**
     * Use Private Key file for authentication
     * @param string|null $privateKeyPath
     * If not provided, it will use default from ENV variable (SSH_PRIVATE_KEY_PATH)
     *
     * @throws RuntimeException
     * @return SshService
     */
    public function usePrivateKey(?string $privateKeyPath = null): self
    {
        if (!$privateKeyPath) {
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
     * Execute commands as a specific user
     * @param string $targetUser
     * @return SshService
     */
    public function asUser(string $targetUser): self
    {
        $this->targetUser = $targetUser;
        return $this;
    }

    /**
     * Establish SSH connection
     * @throws RuntimeException
     */
    protected function connect()
    {
        $this->ssh = new SSH2($this->host, $this->port);
        $key = PublicKeyLoader::load(file_get_contents($this->privateKeyPath));

        if (!$this->ssh->login($this->username, $key)) {
            throw new RuntimeException("SSH Authentication Failed for {$this->username}@{$this->host}");
        }
    }

    /**
     * Execute SSH Commands
     * @param array $commands
     * @throws RuntimeException
     * @return object
     *  ->isSuccessful() bool
     *  ->getOutput() string
     *  ->getErrorOutput() string
     */
    public function execute(array $commands): object
    {
        if (!isset($this->ssh)) {
            $this->connect();
        }

        if ($this->targetUser) {
            // Run all commands in a single sudo execution
            // $commands = implode(" && ", $commands);
            // $commandString = sprintf("sudo -u %s sh -c %s", $this->targetUser, escapeshellarg($commands));
            // Ensure URLs or any special characters are properly quoted
            $commands = array_map(fn($cmd) => str_replace('"', '\"', $cmd), $commands);
            $commandString = sprintf("sudo -u %s sh -c \"%s\"", escapeshellarg($this->targetUser), implode(" && ", $commands));
        } else {
            $commandString = implode(" && ", $commands);
        }

        // $commands = implode(" && ", $commands);
        // dd($commandString);

        $output = $this->ssh->exec($commandString);
        $exitCode = $this->ssh->getExitStatus();

        return new class($exitCode, $output) {
            public bool $success;
            public string $output;

            public function __construct(int $exitCode, string $output)
            {
                $this->success = ($exitCode === 0);
                $this->output = $output;
            }

            public function isSuccessful(): bool
            {
                return $this->success;
            }

            public function getOutput(): string
            {
                return $this->output;
            }
        };
    }
}
