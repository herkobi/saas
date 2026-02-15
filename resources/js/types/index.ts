export * from './auth';
export * from './tenant';
export * from './billing';
export * from './site';
export * from './common';
export * from './navigation';
export * from './ui';
export * from './panel';

import type { Auth } from './auth';
import type { FlashMessage } from './common';
import type { Site } from './site';
import type { Tenant } from './tenant';

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: Auth;
    tenant: Tenant | null;
    site: Site | null;
    flash: FlashMessage;
    [key: string]: unknown;
};
