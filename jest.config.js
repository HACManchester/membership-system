module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/tests/setup.js'],
  moduleNameMapper: {
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '^@inertiajs/react$': '<rootDir>/tests/js/inertia-react.jsx'
  },
  testMatch: [
    "**/__tests__/**/*.{jsx,tsx}",
    "**/?(*.)+(spec|test).{jsx,tsx}"
  ],
  transform: {
    // Resolved from here, not from jest-config, whose own copy of babel-jest runs
    // Babel 7: it would ignore the Babel 8 TypeScript preset in .babelrc.
    "^.+\\.(js|jsx|ts|tsx)$": require.resolve('babel-jest')
  }
};
