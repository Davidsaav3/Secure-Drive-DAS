import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { SolicitudesComponent } from './solicitudes/solicitudes.component';
import { PerfilComponent } from './perfil/perfil.component';
import { InicioComponent } from './inicio/inicio.component';
import { MensajesComponent } from './mensajes/mensajes.component';
import { RegistroComponent } from './registro/registro.component';
import { EntrarComponent } from './entrar/entrar.component';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule } from '@angular/forms';

@NgModule({
  declarations: [
    AppComponent,
    SolicitudesComponent,
    PerfilComponent,
    InicioComponent,
    MensajesComponent,
    RegistroComponent,
    EntrarComponent
  ],
  imports: [
    HttpClientModule,
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }