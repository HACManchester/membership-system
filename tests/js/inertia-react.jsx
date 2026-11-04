// Jest can't load @inertiajs/react, which ships only as an ES module, so
// jest.config.js maps it to this stand-in. Tests set the page with
// jest.mocked(usePage).mockReturnValue(...) and check visits on router.
import { jest } from '@jest/globals';

export const usePage = jest.fn(() => ({ url: '/', props: {} }));

export const router = {
  visit: jest.fn(),
  get: jest.fn(),
  post: jest.fn(),
  put: jest.fn(),
  patch: jest.fn(),
  delete: jest.fn(),
  reload: jest.fn(),
};

// Inertia-only props are dropped so they don't reach the DOM.
// eslint-disable-next-line no-unused-vars
export function Link({ href, method, as, data, preserveState, preserveScroll, only, prefetch, replace, ...rest }) {
  return <a href={href} {...rest} />;
}

export function Head() {
  return null;
}
