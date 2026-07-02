<?php

namespace Tests\Unit;

use App\Support\MemberCardLayout;
use PHPUnit\Framework\TestCase;

class MemberCardLayoutTest extends TestCase
{
    public function test_normalize_available_templates_falls_back_to_all_when_empty(): void
    {
        $this->assertSame(
            MemberCardLayout::allTemplateKeys(),
            MemberCardLayout::normalizeAvailableTemplates([]),
        );
    }

    public function test_normalize_available_templates_filters_unknown_keys(): void
    {
        $this->assertSame(
            ['classic', 'modern'],
            MemberCardLayout::normalizeAvailableTemplates(['classic', 'modern', 'inexistente']),
        );
    }

    public function test_ensure_template_allowed_picks_first_available_when_current_hidden(): void
    {
        $available = ['classic', 'modern'];

        $this->assertSame('classic', MemberCardLayout::ensureTemplateAllowed('crc_vale', $available));
    }

    public function test_template_options_respects_available_list(): void
    {
        $options = MemberCardLayout::templateOptions(['minimal', 'crc_vale']);

        $this->assertSame(['minimal', 'crc_vale'], array_keys($options));
        $this->assertSame('Minimal', $options['minimal']);
    }

    public function test_base_template_keys_excludes_club_layout(): void
    {
        $this->assertContains('classic', MemberCardLayout::baseTemplateKeys());
        $this->assertNotContains('crc_vale', MemberCardLayout::baseTemplateKeys());
    }
}
