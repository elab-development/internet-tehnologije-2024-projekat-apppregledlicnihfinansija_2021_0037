import { Routes, Route, Navigate } from "react-router-dom";
import ProtectedRoute from "./components/ProtectedRoute";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import Transactions from "./pages/Transactions";
import Budgets from "./pages/Budgets";
import Categories from "./pages/Categories";
import SavingsGoals from "./pages/SavingsGoals";
import Profile from "./pages/Profile";
import ResetPassword from "./pages/ResetPassword";
import Alerts from "./pages/Alerts";
import Admin from "./pages/Admin";
import Reports from "./pages/Reports";

export default function App() {
  return (
    <Routes>
      {/* public */}
      <Route path="/login" element={<Login />} />
      <Route path="/register" element={<Register />} />
      <Route path="/reset-password" element={<ResetPassword />} />
      
      {/* protected */}
      <Route path="/" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
      <Route path="/transactions" element={<ProtectedRoute><Transactions /></ProtectedRoute>} />
      <Route path="/budgets" element={<ProtectedRoute><Budgets /></ProtectedRoute>} />
      <Route path="/categories" element={<ProtectedRoute><Categories /></ProtectedRoute>} />
      <Route path="/savings-goals" element={<ProtectedRoute><SavingsGoals /></ProtectedRoute>} />
      <Route path="/profile" element={<ProtectedRoute><Profile /></ProtectedRoute>} />
      
       <Route path="/alerts" element={<ProtectedRoute><Alerts /></ProtectedRoute>} />
       <Route path="/admin" element={<ProtectedRoute><Admin /></ProtectedRoute>} />

       <Route path="/reports" element={<ProtectedRoute><Reports /></ProtectedRoute>} />
     
      
      

      {/* fallback */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

