import { createTheme } from '@mui/material/styles';

const theme = createTheme({
  palette: {
    yellow: {
      main: '#fff000',
      darker: '#e6d800',
    },
  },
  components: {
    MuiCssBaseline: {
      styleOverrides: {
        body: {
          background: '#8e9eab',
          backgroundImage: 'linear-gradient(to bottom, #eef2f3, #8e9eab)',
          backgroundAttachment: 'fixed',
        },
      },
    },
    MuiPaper: {
      styleOverrides: {
        rounded: {
          borderRadius: 16,
        },
      },
    },
  },
  typography: {
    fontFamily: '"Asap", "Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif',
    fontSize: 13,
  },
});

export default theme;