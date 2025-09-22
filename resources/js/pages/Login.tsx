import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { login, setAuthToken } from "../lib/authApi";

const Login = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const navigate = useNavigate();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError("");

        try {
            const response = await login({ email, password });
            setAuthToken(response.token);
            navigate("/dashboard");
        } catch (err: any) {
            if (err.response?.status === 401) {
                setError("Invalid email or password. Please check your credentials and try again.");
            } else if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else {
                setError("Login failed. Please try again.");
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={{
            maxWidth: 400,
            margin: "2rem auto",
            padding: "2rem",
            border: "1px solid #ccc",
            borderRadius: 8,
            boxShadow: "0 2px 10px rgba(0,0,0,0.1)",
            backgroundColor: "#fff"
        }}>
            <h2 style={{ textAlign: "center", marginBottom: "1.5rem", color: "#333" }}>Login</h2>

            {error && (
                <div style={{
                    color: "#d32f2f",
                    backgroundColor: "#ffebee",
                    padding: "0.5rem",
                    borderRadius: 4,
                    marginBottom: "1rem",
                    textAlign: "center"
                }}>
                    {error}
                </div>
            )}

            <form onSubmit={handleSubmit}>
                <div style={{ marginBottom: "1rem" }}>
                    <label htmlFor="email" style={{ display: "block", marginBottom: "0.5rem", fontWeight: "bold" }}>
                        Email:
                    </label>
                    <input
                        type="email"
                        id="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        disabled={loading}
                        style={{
                            width: "100%",
                            padding: "0.75rem",
                            border: "1px solid #ddd",
                            borderRadius: 4,
                            fontSize: "1rem",
                            boxSizing: "border-box"
                        }}
                    />
                </div>
                <div style={{ marginBottom: "1.5rem" }}>
                    <label htmlFor="password" style={{ display: "block", marginBottom: "0.5rem", fontWeight: "bold" }}>
                        Password:
                    </label>
                    <input
                        type="password"
                        id="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        disabled={loading}
                        style={{
                            width: "100%",
                            padding: "0.75rem",
                            border: "1px solid #ddd",
                            borderRadius: 4,
                            fontSize: "1rem",
                            boxSizing: "border-box"
                        }}
                    />
                </div>
                <button
                    type="submit"
                    disabled={loading}
                    style={{
                        width: "100%",
                        padding: "0.75rem",
                        backgroundColor: loading ? "#ccc" : "#007bff",
                        color: "#fff",
                        border: "none",
                        borderRadius: 4,
                        fontSize: "1rem",
                        cursor: loading ? "not-allowed" : "pointer",
                        transition: "background-color 0.2s"
                    }}
                >
                    {loading ? "Logging in..." : "Log In"}
                </button>
            </form>

            <div style={{ textAlign: "center", marginTop: "1rem" }}>
            </div>
        </div>
    );
};

export default Login;
