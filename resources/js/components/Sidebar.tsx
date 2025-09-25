import React, { useEffect, useState } from "react";
import { Link, useLocation } from "react-router-dom";
import { isAuthenticated, getCurrentUser, logout } from "../lib/authApi";

function Sidebar() {
    const location = useLocation();
    const [isAuth, setIsAuth] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const checkAuth = async () => {
            if (isAuthenticated()) {
                try {
                    const user = await getCurrentUser();
                    setIsAuth(!!user);
                } catch {
                    setIsAuth(false);
                }
            } else setIsAuth(false);
            setLoading(false);
        };
        checkAuth();
    }, []);

    const handleLogout = async () => {
        try {
            await logout();
        } catch {
            console.error("Logout failed");
        } finally {
            setIsAuth(false);
            window.location.href = "/";
        }
    };

    if (loading) {
        return <div>Loading...</div>;
    }

    return (
        <div className="sidebar bg-primary text-white p-3" style={{ height: '100vh', overflowY: 'auto' }}>
            <h5 className="mb-4">
                <i className="fas fa-shield-alt me-2"></i>
                INRM System
            </h5>
            <ul className="nav flex-column">
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname === "/" ? "active" : ""}`} to="/">
                        <i className="fas fa-home me-1"></i>
                        Home
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/dashboards") ? "active" : ""}`} to="/dashboards">
                        <i className="fas fa-chart-line me-1"></i>
                        Dashboards
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/audits") ? "active" : ""}`} to="/audits/plans">
                        <i className="fas fa-clipboard-check me-1"></i>
                        Audit Plans
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/assessments") ? "active" : ""}`} to="/assessments">
                        Assessments
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/kris") ? "active" : ""}`} to="/kris">
                        KRIs
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/controls") ? "active" : ""}`} to="/controls">
                        Controls
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/controls/testing") ? "active" : ""}`} to="/controls/testing">
                        Testing Queue
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/controls/analytics") ? "active" : ""}`} to="/controls/analytics">
                        Control Effectiveness
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/frameworks") ? "active" : ""}`} to="/frameworks">
                        Frameworks
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/obligations") ? "active" : ""}`} to="/obligations">
                        Obligations
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/policies") ? "active" : ""}`} to="/policies">
                        Policies
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/my-attestations") ? "active" : ""}`} to="/my-attestations">
                        My Attestations
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/risks") ? "active" : ""}`} to="/risks">
                        <i className="fas fa-exclamation-triangle me-1"></i>
                        Risk Register
                    </Link>
                </li>
            </ul>
            <hr className="my-4" />
            {isAuth ? (
                <div>
                    <button className="btn btn-outline-light w-100 mb-2" onClick={handleLogout}>
                        <i className="fas fa-sign-out-alt me-2"></i>
                        Logout
                    </button>
                </div>
            ) : (
                <Link className="btn btn-outline-light w-100" to="/login">
                    <i className="fas fa-sign-in-alt me-1"></i>
                    Login
                </Link>
            )}
        </div>
    );
}

export default Sidebar;
