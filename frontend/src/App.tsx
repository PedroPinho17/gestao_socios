import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from './auth/AuthContext';
import { ProtectedRoute } from './auth/ProtectedRoute';
import { RequirePasswordChanged } from './auth/RequirePasswordChanged';
import { BrandingProvider } from './branding/BrandingProvider';
import { MemberAreaGate } from './branding/MemberAreaGate';
import { Layout } from './components/Layout';
import { ChangePasswordPage } from './pages/ChangePassword';
import { DashboardPage } from './pages/Dashboard';
import { LoginPage } from './pages/Login';
import { PaymentsPage } from './pages/Payments';

export default function App() {
  return (
    <BrandingProvider>
      <MemberAreaGate>
        <AuthProvider>
          <BrowserRouter>
            <Routes>
              <Route path="/login" element={<LoginPage />} />
              <Route element={<ProtectedRoute />}>
                <Route path="/alterar-password" element={<ChangePasswordPage />} />
                <Route element={<RequirePasswordChanged />}>
                  <Route element={<Layout />}>
                    <Route path="/area-socio" element={<DashboardPage />} />
                    <Route path="/area-socio/pagamentos" element={<PaymentsPage />} />
                  </Route>
                </Route>
              </Route>
              <Route path="*" element={<Navigate to="/area-socio" replace />} />
            </Routes>
          </BrowserRouter>
        </AuthProvider>
      </MemberAreaGate>
    </BrandingProvider>
  );
}
