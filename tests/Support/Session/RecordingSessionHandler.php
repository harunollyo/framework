<?php

namespace Framework\Tests\Support\Session;

use Framework\Contracts\SessionHandler;

/**
 * A session handler that records every driver call so tests can assert on them.
 *
 * Payloads are held in memory; the point is the call log, not persistence.
 */
class RecordingSessionHandler implements SessionHandler
{
    /**
     * The payloads held, keyed by session identifier.
     *
     * @var array<string,array>
     */
    public array $payloads = [];

    /**
     * The identifiers that were read.
     *
     * @var array<int,string>
     */
    public array $reads = [];

    /**
     * The writes, each with the id, payload, and lifetime.
     *
     * @var array<int,array>
     */
    public array $writes = [];

    /**
     * The identifiers that were destroyed.
     *
     * @var array<int,string>
     */
    public array $destroys = [];

    public function read(string $id)
    {
        $this->reads[] = $id;

        return $this->payloads[$id] ?? [];
    }

    public function write(string $id, array $payload, int $lifetime)
    {
        $this->writes[] = ['id' => $id, 'payload' => $payload, 'lifetime' => $lifetime];
        $this->payloads[$id] = $payload;

        return true;
    }

    public function destroy(string $id)
    {
        $this->destroys[] = $id;

        unset($this->payloads[$id]);

        return true;
    }

    /**
     * Seed a stored payload without recording it as a write.
     */
    public function seed(string $id, array $payload): void
    {
        $this->payloads[$id] = $payload;
    }

    /**
     * Get the payload of the most recent write.
     */
    public function last_written_payload(): ?array
    {
        if (empty($this->writes)) {
            return null;
        }

        return $this->writes[count($this->writes) - 1]['payload'];
    }
}
