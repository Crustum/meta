<?php
declare(strict_types=1);

namespace Crustum\Meta;

use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Trait\BuildsHeadTrait;
use Crustum\Meta\Trait\ConditionableTrait;

/**
 * Fluent head builder that writes tag builders onto a head data instance.
 */
class HeadBuilder
{
    use BuildsHeadTrait;
    use ConditionableTrait;

    /**
     * Constructor.
     *
     * @param \Crustum\Meta\HeadData $data Head data to write into
     * @param \Crustum\Meta\Schema\SchemaFactory|null $schemaFactory Schema factory
     */
    public function __construct(
        protected HeadData $data,
        protected ?SchemaFactory $schemaFactory = null,
    ) {
    }

    /**
     * Get the head data the fluent methods write to.
     *
     * @return \Crustum\Meta\HeadData
     */
    protected function headData(): HeadData
    {
        return $this->data;
    }

    /**
     * Get the schema factory, creating one on first use.
     *
     * @return \Crustum\Meta\Schema\SchemaFactory
     */
    protected function schemaFactory(): SchemaFactory
    {
        return $this->schemaFactory ??= new SchemaFactory();
    }
}
