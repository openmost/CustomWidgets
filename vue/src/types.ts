/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface CustomWidget {
  id: number;
  title: string;
  content: string;
  // empty for all websites
  idSites: number[];
}

export interface SiteOption {
  idsite: number;
  name: string;
}
