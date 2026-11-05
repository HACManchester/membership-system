<?php

namespace BB\Entities\Concerns;

/**
 * For models UserAuditObserver watches: changes to the columns the model lists
 * in its $auditFields property are written to the audit log.
 */
trait Audited
{
    /**
     * @return list<string>
     */
    public function getAuditFields(): array
    {
        return $this->auditFields;
    }
}
