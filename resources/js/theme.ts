import { createTheme, ThemeOptions } from '@mui/material/styles';
import { PaletteColorOptions } from '@mui/material';

// Extend the default palette to include a custom 'yellow' color
declare module '@mui/material/styles' {
  interface CustomPalette {
    yellow: PaletteColorOptions;
  }
  
  interface Palette extends CustomPalette {}
  interface PaletteOptions extends CustomPalette {}
}

// Update the Button's props to allow using 'yellow' color
declare module '@mui/material/Button' {
  interface ButtonPropsColorOverrides {
    yellow: true;
  }
}

const theme = createTheme({
  palette: {
    primary: {
      main: "#e6b800", // Slightly darker yellow with better contrast on white
      light: "#fff59d",
      dark: "#b38f00",  // Darker gold shade
      contrastText: "#000"
    },
    secondary: {
      main: "#2D3748", // Dark slate color that pairs well with yellow
      light: "#4A5568",
      dark: "#1A202C",
      contrastText: "#fff"
    },
    yellow: {
      main: '#fff000', // Original brand yellow preserved as reference
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
    MuiLink: {
      defaultProps: {
        color: 'secondary',
        underline: 'hover',
      }
    },
  },
  typography: {
    fontFamily: '"Asap", "Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif',
    fontSize: 13,
  },
} as ThemeOptions);

export default theme;