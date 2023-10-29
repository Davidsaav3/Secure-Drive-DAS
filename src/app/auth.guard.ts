import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}

  canActivate(): boolean {
    if (!this.authService.getAuthenticated()) {
      this.router.navigate(['login']); // Redirige a la página de inicio de sesión si el usuario no está autenticado
      return false;
    }
    return true;
  }
}