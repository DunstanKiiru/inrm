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
        <div className="sidebar-dashboard p-3">
            <h5 className="mb-4">
                <i className="fas fa-shield-alt me-2"></i>
                INRM System
            </h5>
            <ul className="nav flex-column">
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
                        <i className="fas fa-tasks me-1"></i>
                        Assessments
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/kris") ? "active" : ""}`} to="/kris">
                        <i className="fas fa-chart-bar me-1"></i>
                        KRIs
                    </Link>
                </li>
                <li className="nav-item mb-2 dropdown">
                    <a className="nav-link text-white dropdown-toggle" href="#" id="controlsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Controls
                    </a>
                    <ul className="dropdown-menu" aria-labelledby="controlsDropdown">
                        <li><Link className="dropdown-item text-white" to="/controls">Controls</Link></li>
                        <li><Link className="dropdown-item text-white" to="/controls/testing">Testing Queue</Link></li>
                        <li><Link className="dropdown-item text-white" to="/controls/analytics">Control Effectiveness</Link></li>
                    </ul>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/frameworks") ? "active" : ""}`} to="/frameworks">
                        <i className="fas fa-th-large me-1"></i>
                        Frameworks
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/obligations") ? "active" : ""}`} to="/obligations">
                        <i className="fas fa-file-contract me-1"></i>
                        Obligations
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/policies") ? "active" : ""}`} to="/policies">
                        <i className="fas fa-file-alt me-1"></i>
                        Policies
                    </Link>
                </li>
                <li className="nav-item mb-2">
                    <Link className={`nav-link text-white ${location.pathname.startsWith("/my-attestations") ? "active" : ""}`} to="/my-attestations">
                        <i className="fas fa-check-circle me-1"></i>
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
