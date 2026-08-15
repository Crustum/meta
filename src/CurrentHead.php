<?php
declare(strict_types=1);

namespace Crustum\Meta;

/**
 * The request-scoped head data and response status.
 */
class CurrentHead
{
    /**
     * Request-scoped head data.
     *
     * @var \Crustum\Meta\HeadData
     */
    protected HeadData $data;

    /**
     * Response status used to resolve error page head data.
     *
     * @var int|null
     */
    protected ?int $status = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->flush();
    }

    /**
     * Get the request-scoped head data.
     *
     * @return \Crustum\Meta\HeadData
     */
    public function data(): HeadData
    {
        return $this->data;
    }

    /**
     * Get the response status used to resolve error page head data.
     *
     * @return int|null
     */
    public function status(): ?int
    {
        return $this->status;
    }

    /**
     * Set the response status used to resolve error page head data.
     *
     * @param int $status Response status code
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * Flush the head data and status for the current request scope.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->data = new HeadData();
        $this->status = null;
    }
}
