# Tạo type string đúng cho 45 parameters
types = [
    'i',  # 1.  survey_id
    'd',  # 2.  monthly_kwh
    'd',  # 3.  sun_hours
    's',  # 4.  region_name
    'i',  # 5.  panel_id
    's',  # 6.  panel_name
    'd',  # 7.  panel_power
    'd',  # 8.  panel_price
    'i',  # 9.  panels_needed
    'd',  # 10. panel_cost
    'd',  # 11. energy_per_panel_per_day
    'd',  # 12. total_capacity
    'i',  # 13. inverter_id
    's',  # 14. inverter_name
    'd',  # 15. inverter_capacity
    'd',  # 16. inverter_price
    'i',  # 17. cabinet_id
    's',  # 18. cabinet_name
    'd',  # 19. cabinet_capacity
    'd',  # 20. cabinet_price
    'd',  # 21. battery_needed
    's',  # 22. battery_type
    'i',  # 23. battery_id
    's',  # 24. battery_name
    'd',  # 25. battery_capacity
    'i',  # 26. battery_quantity
    'd',  # 27. battery_unit_price
    'd',  # 28. battery_cost
    'i',  # 29. bach_z_qty
    'd',  # 30. bach_z_price
    'd',  # 31. bach_z_cost
    'i',  # 32. clip_qty
    'd',  # 33. clip_price
    'd',  # 34. clip_cost
    'i',  # 35. jack_mc4_qty
    'd',  # 36. jack_mc4_price
    'd',  # 37. jack_mc4_cost
    'i',  # 38. dc_cable_length
    'd',  # 39. dc_cable_price
    'd',  # 40. dc_cable_cost
    'd',  # 41. accessories_cost
    'd',  # 42. labor_cost
    'd',  # 43. total_cost_without_battery
    'd',  # 44. total_cost
    's',  # 45. bill_breakdown
]

type_string = ''.join(types)
print(f"Correct type string ({len(types)} parameters):")
print(type_string)
print(f"\nLength: {len(type_string)} characters")
