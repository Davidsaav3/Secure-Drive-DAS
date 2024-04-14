import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private isAuthenticated: boolean = false;

  constructor(private http: HttpClient) {
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

  sendAuthorizationCode(code: string): Observable<any> {
    const apiUrl = 'https://das-uabook.000webhostapp.com/google2.php'; // Cambia la URL según tu backend
    const requestData = { code: code };
    
    return this.http.post(apiUrl, requestData);
  }
}
