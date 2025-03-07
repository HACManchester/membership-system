module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/tests/setup.js'],
  moduleNameMapper: {
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy'
  },
  testMatch: [
    "**/__tests__/**/*.jsx",
    "**/?(*.)+(spec|test).jsx"
  ],
  transform: {
    "^.+\\.(js|jsx)$": "babel-jest"
  }
};