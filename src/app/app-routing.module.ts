import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { SolicitudesComponent } from './solicitudes/solicitudes.component';
import { PerfilComponent } from './perfil/perfil.component';
import { InicioComponent } from './inicio/inicio.component';
import { MensajesComponent } from './mensajes/mensajes.component';
import { RegistroComponent } from './registro/registro.component';
import { EntrarComponent } from './entrar/entrar.component';
import { AuthGuard } from './auth.guard';

const routes: Routes = [
  { path: 'entrar', component: EntrarComponent },
  { path: 'registro', component: RegistroComponent },
  { path: 'solicitudes', component: SolicitudesComponent , canActivate: [AuthGuard]},
  { path: 'perfil', component: PerfilComponent , canActivate: [AuthGuard]},
  { path: 'inicio', component: InicioComponent , canActivate: [AuthGuard]},
  { path: 'mensajes', component: MensajesComponent , canActivate: [AuthGuard]},
  { path: '', redirectTo: '/entrar', pathMatch: 'full' }, 
  { path: '**', redirectTo: '/entrar' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }