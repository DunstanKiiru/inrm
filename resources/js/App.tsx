import React from "react";
import { BrowserRouter as Router, Routes, Route, Link } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import DashboardsHome from "./pages/DashboardsHome";
import DashboardView from "./pages/DashboardView";
import AuditPlansList from "./pages/AuditPlansList";
import AuditPlanDetail from "./pages/AuditPlanDetail";
import NewAuditPlan from "./pages/NewAuditPlan";

const queryClient = new QueryClient();

function App() {
    return (
        <QueryClientProvider client={queryClient}>
            <Router>
                <div className="min-h-screen bg-gray-100">
                    {/* Navbar */}
                    <nav className="bg-white shadow-lg">
                        <div className="max-w-7xl mx-auto px-4">
                            <div className="flex justify-between h-16">
                                <div className="flex">
                                    <div className="flex-shrink-0 flex items-center">
                                        <h1 className="text-xl font-bold text-gray-800">
                                            Dashboard App
                                        </h1>
                                    </div>
                                    <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                                        <Link
                                            to="/dashboards"
                                            className="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                        >
                                            Dashboards
                                        </Link>
                                        <Link
                                            to="/audits/plans"
                                            className="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                        >
                                            Audit Plans
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>

                    {/* Main content */}
                    <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                        <Routes>
                            <Route path="/" element={<DashboardsHome />} />
                            <Route
                                path="/dashboard"
                                element={<DashboardsHome />}
                            />
                            <Route
                                path="/dashboards"
                                element={<DashboardsHome />}
                            />
                            <Route
                                path="/dashboards/:id"
                                element={<DashboardView />}
                            />
                            <Route
                                path="/audits/plans"
                                element={<AuditPlansList />}
                            />
                            <Route
                                path="/audits/plans/new"
                                element={<NewAuditPlan />}
                            />
                            <Route
                                path="/audits/plans/:id"
                                element={<AuditPlanDetail />}
                            />
                        </Routes>
                    </main>
                </div>
            </Router>
        </QueryClientProvider>
    );
}

export default App;
