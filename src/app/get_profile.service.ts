import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Get_profileService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/get_profile.php';
  
  constructor(private http: HttpClient) {}

  get_profile(username: any): Observable<any> {
    const formData = new FormData();
    const token = localStorage.getItem('token');
    if(token!=null){
      formData.append('token', token);
    }
    formData.append('username', username);
    return this.http.post<any>(this.apiUrl, formData);
  }
}