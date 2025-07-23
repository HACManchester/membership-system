import React from "react";
import ReactMarkdown from "react-markdown";
import { Link, Typography } from "@mui/material";

interface MarkdownRendererProps {
    content: string | null | undefined;
    variant?: "body1" | "body2" | "h1" | "h2" | "h3" | "h4" | "h5" | "h6";
}

const MarkdownRenderer: React.FC<MarkdownRendererProps> = ({
    content,
    variant = "body1",
}) => {
    if (!content) {
        return null;
    }

    return (
        <ReactMarkdown
            components={{
                p: ({ children }) => (
                    <Typography
                        variant={variant}
                        component="div"
                        sx={{ mb: 1 }}
                    >
                        {children}
                    </Typography>
                ),
                a: ({ href, children }) => (
                    <Link
                        href={href}
                        target="_blank"
                        rel="noopener noreferrer"
                        underline="always"
                    >
                        {children}
                    </Link>
                ),
                ul: ({ children }) => (
                    <Typography
                        variant={variant}
                        component="ul"
                        sx={{ pl: 2, mb: 1 }}
                    >
                        {children}
                    </Typography>
                ),
                ol: ({ children }) => (
                    <Typography
                        variant={variant}
                        component="ol"
                        sx={{ pl: 2, mb: 1 }}
                    >
                        {children}
                    </Typography>
                ),
                li: ({ children }) => (
                    <Typography
                        variant={variant}
                        component="li"
                        sx={{ mb: 0.5 }}
                    >
                        {children}
                    </Typography>
                ),
                h1: ({ children }) => (
                    <Typography variant="h4" sx={{ mb: 2, mt: 2 }}>
                        {children}
                    </Typography>
                ),
                h2: ({ children }) => (
                    <Typography variant="h5" sx={{ mb: 1.5, mt: 1.5 }}>
                        {children}
                    </Typography>
                ),
                h3: ({ children }) => (
                    <Typography variant="h6" sx={{ mb: 1, mt: 1 }}>
                        {children}
                    </Typography>
                ),
                strong: ({ children }) => <strong>{children}</strong>,
                em: ({ children }) => <em>{children}</em>,
                code: ({ children }) => (
                    <code
                        style={{
                            backgroundColor: "#f5f5f5",
                            padding: "2px 4px",
                            borderRadius: "3px",
                            fontFamily: "monospace",
                        }}
                    >
                        {children}
                    </code>
                ),
                pre: ({ children }) => (
                    <pre
                        style={{
                            backgroundColor: "#f5f5f5",
                            padding: "16px",
                            borderRadius: "4px",
                            overflowX: "auto",
                            marginBottom: "16px",
                        }}
                    >
                        {children}
                    </pre>
                ),
            }}
        >
            {content}
        </ReactMarkdown>
    );
};

export default MarkdownRenderer;
