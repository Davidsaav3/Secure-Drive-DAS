import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private isAuthenticated: boolean = false;

  constructor() { }

  setAuthenticated(value: boolean) {
    this.isAuthenticated = value;
  }

  getAuthenticated(): boolean {
    return this.isAuthenticated;
  }
}