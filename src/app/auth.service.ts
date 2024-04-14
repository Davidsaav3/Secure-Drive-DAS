import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private isAuthenticated: boolean = false;

  constructor() {
    const authValue = localStorage.getItem('auth');
    this.isAuthenticated = authValue !== null && Boolean(authValue);
    if (authValue === null) {
      localStorage.setItem('auth', this.isAuthenticated.toString());
    }
  }

  setAuthenticated(value: boolean) {
    localStorage.setItem('auth', value.toString());
    this.isAuthenticated = value;
  }

  getAuthenticated(): boolean {
    const authValue = localStorage.getItem('auth');
    this.isAuthenticated = authValue !== null && Boolean(authValue);
    return this.isAuthenticated;
  }

  logout() {
    localStorage.removeItem('auth');
    this.isAuthenticated = false;
  }
}
