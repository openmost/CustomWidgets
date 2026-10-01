/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// Keep in sync with WidgetDefinitions::formatContent(), the preview must render like the dashboards
const INVISIBLE_ELEMENTS_PATTERN = /<(script|style|template)\b[\s\S]*?<\/\1\s*>/gi;

const BLOCK_TAG_PATTERN = /<(address|article|aside|audio|blockquote|canvas|details|div|dl|fieldset|figure|footer|form|h[1-6]|header|hr|iframe|main|nav|ol|p|pre|section|svg|table|ul|video)[\s>/]/i;

export default function formatContent(content: string): string {
  const trimmed = content.trim();
  const visibleText = trimmed.replace(INVISIBLE_ELEMENTS_PATTERN, '').replace(/<[^>]*>/g, '').trim();
  if (visibleText === '' || BLOCK_TAG_PATTERN.test(trimmed)) {
    return trimmed;
  }

  return trimmed
    .split(/\r?\n\s*\r?\n/)
    .map((paragraph) => `<p>${paragraph.trim().replace(/\r?\n/g, '<br>')}</p>`)
    .join('');
}
