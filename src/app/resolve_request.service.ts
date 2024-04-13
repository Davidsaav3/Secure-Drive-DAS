import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Resolve_requestService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/resolve_request.php';
  
  constructor(private http: HttpClient) {}

  resolve_request(id_user: any): Observable<any> {
    const formData = new FormData();
    formData.append('id_user', id_user);
    return this.http.post<any>(this.apiUrl, formData);
  }
}