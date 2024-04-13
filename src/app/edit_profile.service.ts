import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Edit_profileService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/edit_profile.php';
  
  constructor(private http: HttpClient) {}

  edit_profile(id_user: any): Observable<any> {
    const formData = new FormData();
    formData.append('id_user', id_user);
    return this.http.post<any>(this.apiUrl, formData);
  }
}