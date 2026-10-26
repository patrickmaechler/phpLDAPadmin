<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TreeCreateNodePolicyTest extends TestCase
{
    public function test_cn_and_uid_entries_do_not_allow_create_children(): void
    {
        $this->assertFalse(can_create_children_in_tree('uid=alice,dc=example,dc=com'));
        $this->assertFalse(can_create_children_in_tree('cn=Alice,ou=People,dc=example,dc=com'));
    }

    public function test_other_rdn_types_still_allow_create_children(): void
    {
        $this->assertTrue(can_create_children_in_tree('ou=People,dc=example,dc=com'));
        $this->assertTrue(can_create_children_in_tree('dc=example,dc=com'));
    }
}
