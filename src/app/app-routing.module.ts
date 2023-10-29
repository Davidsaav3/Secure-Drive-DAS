import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { AuthGuard } from './auth.guard';

const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'home', component: HomeComponent , canActivate: [AuthGuard]},
  { path: '', redirectTo: '/login', pathMatch: 'full' }, // Redirige a la página de inicio de sesión por defecto
  { path: '**', redirectTo: '/login' } // Redirige a la página de inicio de sesión si la URL no coincide con ninguna ruta definida
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }