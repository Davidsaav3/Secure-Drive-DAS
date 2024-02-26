import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { PerfilComponent } from './perfil/perfil.component';
import { InicioComponent } from './inicio/inicio.component';
import { RegistroComponent } from './registro/registro.component';
import { EntrarComponent } from './entrar/entrar.component';
import { AuthGuard } from './auth.guard';
import { AppRoutes } from './routes';

const routes: Routes = [
  { path: AppRoutes.entrar, component: EntrarComponent },
  { path: AppRoutes.registro, component: RegistroComponent },
  { path: `${AppRoutes.perfil}/:nombreDeUsuario`, component: PerfilComponent, canActivate: [AuthGuard] },
  { path: AppRoutes.inicio, component: InicioComponent, canActivate: [AuthGuard] },
  { path: AppRoutes.redirectToInicio, redirectTo: AppRoutes.inicioCompleto, pathMatch: 'full' },
  { path: AppRoutes.inicioCompletoWildCard, redirectTo: AppRoutes.inicioCompleto }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }